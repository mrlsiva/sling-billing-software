<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\Size;
use App\Models\Colour;
use App\Models\Stock;

class PurchaseBulkImport implements ToCollection
{
    protected $requestData;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    public function collection(Collection $rows)
    {
        $products = [];
        $allImeis = [];
        $grouped = [];

        foreach ($rows as $index => $row) {

            // Skip header
            if ($index == 0) continue;

            $categoryName    = trim($row[0]);
            $subCategoryName = trim($row[1]);
            $productName     = trim($row[2]);
            $sizeName        = trim($row[3]);
            $colourName      = trim($row[4]);
            $qty             = (float)$row[5];
            $discount        = (float)$row[6];
            $imeiRaw         = $row[7];

            // ✅ Find IDs
            $category = Category::where('name', $categoryName)->first();
            $subCategory = SubCategory::where('name', $subCategoryName)->first();
            $product = Product::where('name', $productName)->first();

            $errors = [];

            if (!$category) {
                $errors[] = "Category '{$categoryName}' not found";
            }

            if (!$subCategory) {
                $errors[] = "Sub Category '{$subCategoryName}' not found";
            }

            if (!$product) {
                $errors[] = "Product '{$productName}' not found";
            }

            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // ✅ Price & Tax from product
            $price = $product->price ?? 0;
            $tax = 0;

            if ($product->tax) {
                $tax = $product->tax->name ?? $product->tax->name ?? 0;
            }

            // ✅ Calculation (same as JS)
            $net = $qty * $price;
            $taxAmount = $net * $tax / 100;
            $gross = $net + $taxAmount - $discount;

            // ✅ IMEI handling
            // ✅ IMEI handling
            $imeiList = !empty($imeiRaw) ? explode(',', $imeiRaw) : [];
            $imeiList = array_filter(array_map('trim', $imeiList));

            // ✅ Validate IMEI count vs quantity
            if (count($imeiList) > $qty) {
                throw new \Exception("IMEI count (".count($imeiList).") exceeds Quantity ($qty) for product '{$productName}'");
            }

            foreach ($imeiList as $imei) {

                if (!preg_match('/^[0-9]{1,15}$/', $imei)) {
                    throw new \Exception("Invalid IMEI '{$imei}' for product '{$productName}'");
                }

                if (in_array($imei, $allImeis)) {
                    throw new \Exception("Duplicate IMEI '{$imei}' found in file");
                }

                $allImeis[] = $imei;
            }

            // ✅ Size & Colour (convert to IDs if needed)
            $variation = [];

            if ($sizeName || $colourName) {
                $stock = Stock::where([
                    'shop_id'        => auth()->user()->owner_id,
                    'branch_id'      => null,
                    'category_id'    => $category->id,
                    'sub_category_id'=> $subCategory->id,
                    'product_id'     => $product->id,
                ])->first();

                $variation[] = [
                    'stock_id'  => $stock->id ?? null, // ✅ FIX
                    'size_id'   => $this->getSizeId($sizeName),
                    'colour_id' => $this->getColourId($colourName),
                    'qty'       => $qty,
                ];
            }

            

            $key = $category->id.'-'.$subCategory->id.'-'.$product->id;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category'       => $category->id,
                    'sub_category'   => $subCategory->id,
                    'product'        => $product->id,
                    'unit'           => $product->metric_id,
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

            // ✅ Add quantity
            $grouped[$key]['quantity'] += $qty;

            // ✅ Add discount
            $grouped[$key]['discount'] += $discount;

            // ✅ Merge IMEI
            $grouped[$key]['imei'] = array_merge($grouped[$key]['imei'], $imeiList);

            // ✅ Collect size & colour
            if ($sizeId = $this->getSizeId($sizeName)) {
                $grouped[$key]['size_list'][] = $sizeId;
            }

            if ($colourId = $this->getColourId($colourName)) {
                $grouped[$key]['colour_list'][] = $colourId;
            }

            // ✅ Add variation row (important for stock logic)
            $grouped[$key]['variation'][] = [
                'stock_id'  => optional($stock)->id,
                'size_id'   => $this->getSizeId($sizeName),
                'colour_id' => $this->getColourId($colourName),
                'qty'       => $qty,
            ];

            
        }

        foreach ($grouped as &$item) {

                // remove duplicates
            $item['size_list']   = array_unique($item['size_list']);
            $item['colour_list'] = array_unique($item['colour_list']);
            $item['imei']        = array_unique($item['imei']);

            // ✅ Recalculate totals
            $net = $item['quantity'] * $item['price_per_unit'];
            $taxAmount = $net * $item['tax'] / 100;
            $gross = $net + $taxAmount - $item['discount'];

            $item['net_cost']   = $net;
            $item['gross_cost'] = $gross;
        }

        $products = array_values($grouped);

        // ✅ Merge into request and call SAME STORE LOGIC
        $request = new \Illuminate\Http\Request(array_merge(
            $this->requestData,
            ['products' => $products]
        ));

        app()->make(\App\Http\Controllers\users\purchaseOrderController::class)->store($request);
    }

    private function getSizeId($name)
    {
        if (!$name) return null;
        return Size::where('name', $name)->value('id');
    }

    private function getColourId($name)
    {
        if (!$name) return null;
        return Colour::where('name', $name)->value('id');
    }
}
