<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\StockVariation;
use App\Models\ProductHistory;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Colour;
use App\Models\Stock;
use App\Models\Size;

class ProductTransferImport implements ToCollection
{
    public array $errors = [];
    public array $validRows = [];
    protected $ownerId;

    public function __construct($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    public function collection(Collection $rows)
    {
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

                $category = Category::where('name', trim($categoryName))->firstOrFail();
                $subCategory = SubCategory::where('name', trim($subCategoryName))->firstOrFail();
                $product = Product::where('name', trim($productName))->firstOrFail();

                $this->validRows[] = [
                    'category_id'     => $category->id,
                    'sub_category_id' => $subCategory->id,
                    'product_id'      => $product->id,
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

