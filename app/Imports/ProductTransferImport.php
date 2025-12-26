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

    protected $ownerId;

    public function __construct($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    public function collection(Collection $rows)
    {
        $productTotals = [];
        $excelImeis    = [];

        foreach ($rows as $index => $row) {

            if ($index === 0) continue; // header

            $rowNo = $index + 1;

            [
                $categoryName,
                $subCategoryName,
                $productName,
                $imei,
                $sizeName,
                $colourName,
                $quantity
            ] = $row;

            try {

                if (!$quantity || $quantity <= 0) {
                    throw new \Exception('Quantity must be greater than 0');
                }

                $category = Category::where('name', trim($categoryName))->first();
                if (!$category) throw new \Exception("Invalid category");

                $subCategory = SubCategory::where('name', trim($subCategoryName))->first();
                if (!$subCategory) throw new \Exception("Invalid sub category");

                $product = Product::where('name', trim($productName))->first();
                if (!$product) throw new \Exception("Invalid product");

                // Quantity total per product
                $productTotals[$product->id] =
                    ($productTotals[$product->id] ?? 0) + $quantity;

                if ($productTotals[$product->id] > $product->quantity) {
                    throw new \Exception("Total quantity exceeds product stock");
                }

                // IMEI validation
                $imeis = $imei ? array_map('trim', explode(',', $imei)) : [];

                if (count($imeis) > $quantity) {
                    throw new \Exception("IMEI count greater than quantity");
                }

                foreach ($imeis as $i) {

                    if (isset($excelImeis[$i])) {
                        throw new \Exception("Duplicate IMEI in Excel: {$i}");
                    }

                    $excelImeis[$i] = true;

                    $exists = Stock::where([
                        ['shop_id', $this->ownerId],
                        ['branch_id', null],
                        ['product_id', $product->id]
                    ])->whereRaw("FIND_IN_SET(?, imei)", [$i])->exists();

                    if (!$exists) {
                        throw new \Exception("IMEI {$i} does not belong to this product");
                    }
                }

                if ($sizeName && !Size::where('name', trim($sizeName))->exists()) {
                    throw new \Exception("Invalid size");
                }

                if ($colourName && !Colour::where('name', trim($colourName))->exists()) {
                    throw new \Exception("Invalid colour");
                }

            } catch (\Exception $e) {

                $this->errors[] = [
                    'Row'      => $rowNo,
                    'Category' => $categoryName,
                    'Product'  => $productName,
                    'Error'    => $e->getMessage()
                ];
            }
        }
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
