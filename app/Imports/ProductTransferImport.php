<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\Colour;
use App\Models\StockVariation;

class ProductTransferImport implements ToCollection
{
    public array $errors    = [];
    public array $validRows = [];
    protected $ownerId;

    // In-memory lookup caches
    protected array $categories    = [];
    protected array $subCategories = [];
    protected array $products      = [];
    protected array $sizes   = [];
    protected array $colours = [];

    public function __construct($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    protected function preload(): void
    {
        // Categories
        $this->categories = Category::all()
            ->keyBy(fn($c) => strtolower(trim($c->name)))
            ->toArray();

        // SubCategories
        $this->subCategories = SubCategory::with('category')->get()
            ->keyBy(function ($s) {
                return strtolower(trim($s->category->name)) . '|' . strtolower(trim($s->name));
            })
            ->toArray();

        // Products
        $this->products = Product::with(['sub_category.category'])->get()
            ->keyBy(function ($p) {

                // 🔴 SAFETY CHECK (important)
                if (!$p->sub_category || !$p->sub_category->category) {
                    return null; // skip invalid records
                }

                return strtolower(trim($p->sub_category->category->name)) . '|'
                     . strtolower(trim($p->sub_category->name)) . '|'
                     . strtolower(trim($p->name));
            })
            ->filter() // removes null keys
            ->toArray();

        $this->sizes = Size::where('shop_id', $this->ownerId)
            ->get()
            ->keyBy(fn($s) => strtolower(trim($s->name)))
            ->toArray();

        $this->colours = Colour::where('shop_id', $this->ownerId)
            ->get()
            ->keyBy(fn($c) => strtolower(trim($c->name)))
            ->toArray();
    }

    public function collection(Collection $rows)
    {
        // Pre-load all lookup data once — avoids N+1 queries per row
        $this->preload();

        foreach ($rows as $index => $row) {

            if ($index === 0) continue;

            try {

                $row = $row->toArray();

                // Excel WITH IMEI column
                if (count($row) >= 7) {

                    $categoryName    = $row[0] ?? null;
                    $subCategoryName = $row[1] ?? null;
                    $productName     = $row[2] ?? null;
                    $imei            = $row[3] ?? null;
                    $sizeName        = $row[4] ?? null;
                    $colourName      = $row[5] ?? null;
                    $quantity        = $row[6] ?? null;

                }
                // Excel WITHOUT IMEI column
                else {

                    $categoryName    = $row[0] ?? null;
                    $subCategoryName = $row[1] ?? null;
                    $productName     = $row[2] ?? null;
                    $imei            = null;
                    $sizeName        = $row[3] ?? null;
                    $colourName      = $row[4] ?? null;
                    $quantity        = $row[5] ?? null;
                }
                $categoryKey = strtolower(trim($categoryName));

                $subCategoryKey = $categoryKey . '|' . strtolower(trim($subCategoryName));

                $productKey = $categoryKey . '|'
                            . strtolower(trim($subCategoryName)) . '|'
                            . strtolower(trim($productName));

                $category    = $this->categories[$categoryKey] ?? null;
                $subCategory = $this->subCategories[$subCategoryKey] ?? null;
                $product     = $this->products[$productKey] ?? null;

                if (!$category) {
                    throw new \Exception("Category '$categoryName' not found");
                }

                if (!$subCategory) {
                    throw new \Exception("Sub Category '$subCategoryName' not found");
                }

                if (!$product) {
                    throw new \Exception("Product '$productName' not found");
                }

                $size = null;
                $colour = null;

                if (!empty($sizeName)) {
                    $sizeKey = strtolower(trim($sizeName));
                    $size = $this->sizes[$sizeKey] ?? null;

                    if (!$size) {
                        throw new \Exception("Size '$sizeName' not found");
                    }
                }

                if (!empty($colourName)) {
                    $colourKey = strtolower(trim($colourName));
                    $colour = $this->colours[$colourKey] ?? null;

                    if (!$colour) {
                        throw new \Exception("Colour '$colourName' not found");
                    }
                }

                $variation = null;

                if ($size || $colour) {

                    $variation = StockVariation::where([
                        ['product_id', $product['id']],
                        ['size_id', $size['id'] ?? null],
                        ['colour_id', $colour['id'] ?? null],
                    ])->first();

                    if (!$variation) {
                        throw new \Exception("Variation not found for Size '{$sizeName}' and Colour '{$colourName}'");
                    }
                }

                $this->validRows[] = [
                    'category_id'     => $category['id'],
                    'sub_category_id' => $subCategory['id'],
                    'product_id'      => $product['id'],
                    'quantity'        => (int) $quantity,
                    'imeis'           => $imei ? explode(',', $imei) : [],

                    // ✅ ADD THESE
                    'variation_id' => $variation->id ?? null,
                    'size_id'      => $size['id'] ?? null,
                    'colour_id'    => $colour['id'] ?? null,
                ];

            } catch (\Exception $e) {

                $this->errors[] = [
                    'row'   => $index + 1,
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getRowCount(): int
    {
        return count($this->validRows) + count($this->errors);
    }
}
