<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;

class ProductTransferImport implements ToCollection
{
    public array $errors    = [];
    public array $validRows = [];
    protected $ownerId;

    // In-memory lookup caches
    protected array $categories    = [];
    protected array $subCategories = [];
    protected array $products      = [];

    public function __construct($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    protected function preload(): void
    {
        $this->categories    = Category::all()->keyBy(fn($c) => strtolower(trim($c->name)))->toArray();
        $this->subCategories = SubCategory::all()->keyBy(fn($s) => strtolower(trim($s->name)))->toArray();
        $this->products      = Product::all()->keyBy(fn($p) => strtolower(trim($p->name)))->toArray();
    }

    public function collection(Collection $rows)
    {
        // Pre-load all lookup data once — avoids N+1 queries per row
        $this->preload();

        foreach ($rows as $index => $row) {

            if ($index === 0) continue;

            try {

                [
                    $categoryName,
                    $subCategoryName,
                    $productName,
                    $imei,
                    $sizeName,
                    $colourName,
                    $quantity
                ] = $row;

                $category    = $this->categories[strtolower(trim($categoryName))] ?? null;
                $subCategory = $this->subCategories[strtolower(trim($subCategoryName))] ?? null;
                $product     = $this->products[strtolower(trim($productName))] ?? null;

                if (!$category) {
                    throw new \Exception("Category '$categoryName' not found");
                }

                if (!$subCategory) {
                    throw new \Exception("Sub Category '$subCategoryName' not found");
                }

                if (!$product) {
                    throw new \Exception("Product '$productName' not found");
                }

                $this->validRows[] = [
                    'category_id'     => $category['id'],
                    'sub_category_id' => $subCategory['id'],
                    'product_id'      => $product['id'],
                    'quantity'        => (int) $quantity,
                    'imeis'           => $imei ? explode(',', $imei) : [],
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
