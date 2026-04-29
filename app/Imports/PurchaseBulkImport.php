<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\Size;
use App\Models\Colour;
use App\Models\Stock;
use App\Models\Tax;

class PurchaseBulkImport implements ToCollection
{
    protected $requestData;

    // In-memory lookup caches
    protected $categories  = [];
    protected $subCategories = [];
    protected $products    = [];
    protected $taxes       = [];
    protected $sizes       = [];
    protected $colours     = [];

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * Pre-load all lookup tables into memory before processing rows.
     */
    protected function preload()
    {
        $shopId = auth()->user()->owner_id;

        // Categories
        $this->categories = Category::all()
            ->keyBy(fn($c) => strtolower(trim($c->name)))
            ->toArray();

        // SubCategories (category + subcategory)
        $this->subCategories = SubCategory::with('category')->get()
            ->keyBy(function ($s) {
                return strtolower(trim($s->category->name)) . '|' . strtolower(trim($s->name));
            })
            ->toArray();

        // Products (category + subcategory + product)
        $this->products = Product::with(['sub_category.category'])->get()
            ->keyBy(function ($p) {

                if (!$p->sub_category || !$p->sub_category->category) {
                    return null;
                }

                return strtolower(trim($p->sub_category->category->name)) . '|'
                     . strtolower(trim($p->sub_category->name)) . '|'
                     . strtolower(trim($p->name));
            })
            ->filter()
            ->toArray();

        // Taxes
        $this->taxes = Tax::where('shop_id', $shopId)->get()
            ->keyBy(fn($t) => strtolower(trim($t->name)))
            ->toArray();

        // Sizes
        $this->sizes = Size::all()
            ->keyBy(fn($s) => strtolower(trim($s->name)))
            ->toArray();

        // Colours
        $this->colours = Colour::all()
            ->keyBy(fn($c) => strtolower(trim($c->name)))
            ->toArray();
    }

    public function collection(Collection $rows)
    {
        // Pre-load all lookup data once (avoids N+1 queries per row)
        $this->preload();

        $allImeis  = [];
        $grouped   = [];
        $stockCache = []; // cache stock lookups by composite key

        foreach ($rows as $index => $row) {

            // Skip header
            if ($index == 0) continue;

            $categoryName    = trim($row[0]);
            $subCategoryName = trim($row[1]);
            $productName     = trim($row[2]);
            $sizeName        = trim($row[3]);
            $colourName      = trim($row[4]);
            $qty             = (float)$row[5];
            $price           = (float)$row[6];
            $taxInput        = (float)$row[7];
            $discount        = (float)$row[8];
            $imeiRaw         = $row[9];

            // ✅ In-memory lookups (no DB queries per row)
            $categoryKey = strtolower($categoryName);

            $subCategoryKey = $categoryKey . '|' . strtolower($subCategoryName);

            $productKey = $categoryKey . '|'
                        . strtolower($subCategoryName) . '|'
                        . strtolower($productName);

            $category    = $this->categories[$categoryKey] ?? null;
            $subCategory = $this->subCategories[$subCategoryKey] ?? null;
            $product     = $this->products[$productKey] ?? null;
            $taxModel    = $this->taxes[strtolower((string)$taxInput)] ?? null;

            $errors = [];

            if (!$category) {
                $errors[] = "Category '$categoryName' not found";
            }

            if (!$subCategory) {
                $errors[] = "Sub Category '$subCategoryName' not found";
            }

            if (!$product) {
                $errors[] = "Product '$productName' not found";
            }

            if (!$taxModel) {
                $errors[] = "Tax {$taxInput}% not found in shop for product '$productName'";
            }

            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            $tax = $taxModel['name'];

            // ✅ Calculation
            $net       = $qty * $price;
            $taxAmount = $net * $tax / 100;
            $gross     = $net + $taxAmount - $discount;

            // ✅ IMEI handling
            $imeiList = !empty($imeiRaw) ? explode(',', $imeiRaw) : [];
            $imeiList = array_filter(array_map('trim', $imeiList));

            if (count($imeiList) > $qty) {
                throw new \Exception("IMEI count (".count($imeiList).") exceeds Quantity ($qty) for product '$productName'");
            }

            foreach ($imeiList as $imei) {
                if (!preg_match('/^[0-9]{1,15}$/', $imei)) {
                    throw new \Exception("Invalid IMEI '$imei' for product '$productName'");
                }
                if (in_array($imei, $allImeis)) {
                    throw new \Exception("Duplicate IMEI '$imei' found in file");
                }
                $allImeis[] = $imei;
            }

            // ✅ Size & Colour from in-memory cache
            $sizeId   = $this->getSizeId($sizeName);
            $colourId = $this->getColourId($colourName);

            // ✅ Stock lookup with cache (one DB query per unique product)
            $stock = null;
            if ($sizeName || $colourName) {
                $stockKey = $category['id'].'-'.$subCategory['id'].'-'.$product['id'];
                if (!isset($stockCache[$stockKey])) {
                    $stockCache[$stockKey] = Stock::where([
                        'shop_id'         => auth()->user()->owner_id,
                        'branch_id'       => null,
                        'category_id'     => $category['id'],
                        'sub_category_id' => $subCategory['id'],
                        'product_id'      => $product['id'],
                    ])->first();
                }
                $stock = $stockCache[$stockKey];
            }

            $key = $category['id'].'-'.$subCategory['id'].'-'.$product['id'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category'       => $category['id'],
                    'sub_category'   => $subCategory['id'],
                    'product'        => $product['id'],
                    'unit'           => $product['metric_id'],
                    'quantity'       => 0,
                    'price_per_unit' => $price,
                    'tax'            => $tax,
                    'discount'       => 0,
                    'net_cost'       => 0,
                    'gross_cost'     => 0,
                    'imei'           => [],
                    'variation'      => [],
                    'size_list'      => [],
                    'colour_list'    => [],
                ];
            }

            $grouped[$key]['quantity'] += $qty;
            $grouped[$key]['discount'] += $discount;
            $grouped[$key]['imei']      = array_merge($grouped[$key]['imei'], $imeiList);

            if ($sizeId) {
                $grouped[$key]['size_list'][] = $sizeId;
            }

            if ($colourId) {
                $grouped[$key]['colour_list'][] = $colourId;
            }

            if ($stock) {
                $grouped[$key]['variation'][] = [
                    'stock_id'  => $stock->id,
                    'size_id'   => $sizeId,
                    'colour_id' => $colourId,
                    'qty'       => $qty,
                ];
            }
        }

        foreach ($grouped as &$item) {
            $item['size_list']   = array_unique($item['size_list']);
            $item['colour_list'] = array_unique($item['colour_list']);
            $item['imei']        = array_unique($item['imei']);

            $net       = $item['quantity'] * $item['price_per_unit'];
            $taxAmount = $net * $item['tax'] / 100;
            $gross     = $net + $taxAmount - $item['discount'];

            $item['net_cost']   = $net;
            $item['gross_cost'] = $gross;
        }

        $products = array_values($grouped);

        $controller = app()->make(\App\Http\Controllers\users\purchaseOrderController::class);

        DB::beginTransaction();

        try {
            [$lastOrder, $totalGross, $vendor] = $controller->processOrderStorage(
                $this->requestData['vendor'],
                null,
                $this->requestData['invoice'],
                $this->requestData['invoice_date'],
                $this->requestData['due_date'] ?? null,
                $products
            );

            // --- Handle prepaid balance ---
            $prepaid = $vendor->prepaid_amount ?? 0;

            if ($prepaid > 0 && $lastOrder) {
                if ($prepaid >= $totalGross) {
                    $allocatable = $totalGross;
                    $vendor->update(['prepaid_amount' => $prepaid - $totalGross]);
                    $comment = 'Fully paid using prepaid balance';
                    $status  = 1;
                } else {
                    $allocatable = $prepaid;
                    $vendor->update(['prepaid_amount' => 0]);
                    $comment = 'Partially paid using prepaid balance';
                    $status  = 2;
                }

                \App\Models\VendorPaymentDetail::create([
                    'vendor_payment_id' => null,
                    'purchase_order_id' => $lastOrder->id,
                    'payment_id'        => 1,
                    'amount'            => $allocatable,
                    'paid_on'           => now(),
                    'comment'           => $comment,
                ]);

                \App\Models\PurchaseOrder::where('invoice_no', $this->requestData['invoice'])
                    ->update(['status' => $status]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getSizeId($name)
    {
        if (!$name) return null;
        return $this->sizes[strtolower($name)]['id'] ?? null;
    }

    private function getColourId($name)
    {
        if (!$name) return null;
        return $this->colours[strtolower($name)]['id'] ?? null;
    }
}
