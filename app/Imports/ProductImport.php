<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\Metric;
use App\Models\Stock;
use App\Models\Size;
use App\Models\Colour;
use App\Models\StockVariation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    private int $rowCount = 0;
    private int $runId;
    private array $currentRow = [];

    // In-memory caches to avoid repeated DB lookups per row
    private array $categoryCache    = [];
    private array $subCategoryCache = [];
    private array $taxCache         = [];
    private array $metricCache      = [];
    private array $sizeCache   = [];
    private array $colourCache = [];

    public function __construct(int $runId)
    {
        $this->runId = $runId;
    }

    private function getCategory(int $userId, string $name): ?object
    {
        $key = $userId . '|' . strtolower(trim($name));
        if (!array_key_exists($key, $this->categoryCache)) {
            $this->categoryCache[$key] = Category::where('user_id', $userId)
                ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();
        }
        return $this->categoryCache[$key];
    }

    private function getSubCategory(int $userId, int $categoryId, string $name): ?object
    {
        $key = $userId . '|' . $categoryId . '|' . strtolower(trim($name));
        if (!array_key_exists($key, $this->subCategoryCache)) {
            $this->subCategoryCache[$key] = SubCategory::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();
        }
        return $this->subCategoryCache[$key];
    }

    private function getTax(int $userId, string $name): ?object
    {
        $key = $userId . '|' . strtolower(trim($name));
        if (!array_key_exists($key, $this->taxCache)) {
            $this->taxCache[$key] = Tax::where('shop_id', $userId)
                ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();
        }
        return $this->taxCache[$key];
    }

    private function getMetric(int $userId, string $name): ?object
    {
        $key = $userId . '|' . strtolower(trim($name));
        if (!array_key_exists($key, $this->metricCache)) {
            $this->metricCache[$key] = Metric::where('shop_id', $userId)
                ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();
        }
        return $this->metricCache[$key];
    }

    // Keep row data for validation closures
    public function prepareForValidation($data, $index)
    {
        $this->currentRow = $data;
        return $data;
    }

    private function getSize(int $shopId, string $name): ?object
    {
        $key = $shopId . '|' . strtolower(trim($name));

        if (!array_key_exists($key, $this->sizeCache)) {
            $this->sizeCache[$key] = Size::where('shop_id', $shopId)
                ->whereRaw('LOWER(name)=?', [strtolower(trim($name))])
                ->first();
        }

        return $this->sizeCache[$key];
    }

    private function getColour(int $shopId, string $name): ?object
    {
        $key = $shopId . '|' . strtolower(trim($name));

        if (!array_key_exists($key, $this->colourCache)) {
            $this->colourCache[$key] = Colour::where('shop_id', $shopId)
                ->whereRaw('LOWER(name)=?', [strtolower(trim($name))])
                ->first();
        }

        return $this->colourCache[$key];
    }

    public function model(array $row)
    {
        $this->rowCount++;
        $userId = Auth::id();

        $category = $this->getCategory($userId, $row['category']);
        if (!$category) return null;

        $subCategory = $this->getSubCategory($userId, $category->id, $row['sub_category']);
        if (!$subCategory) return null;

        $tax = $this->getTax($userId, $row['tax']);
        if (!$tax) return null;

        $metric = $this->getMetric($userId, $row['metric']);
        if (!$metric) return null;

        // Skip duplicate product
        $exists = Product::where('user_id', $userId)
            ->where('category_id', $category->id)
            ->where('sub_category_id', $subCategory->id)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($row['name']))])
            ->exists();
        if ($exists) return null;

        $discountType = !empty($row['discount_type']) 
            ? (strtolower(trim($row['discount_type'])) === 'flat' ? 1 : 2) 
            : null;

        $price = (float) $row['price'];
        $taxAmount = $tax ? ($price * ((float)$tax->name / 100)) : 0;

        $isSize   = strtolower(trim($row['is_size_differentiation_available'] ?? 'no')) === 'yes' ? 1 : 0;
        $isColour = strtolower(trim($row['is_colour_differentiation_available'] ?? 'no')) === 'yes' ? 1 : 0;

        $sizeNames   = !empty($row['size']) ? array_map('trim', explode(',', $row['size'])) : [];
        $colourNames = !empty($row['colour']) ? array_map('trim', explode(',', $row['colour'])) : [];

        $sizeIds = [];
        foreach ($sizeNames as $name) {
            $size = $this->getSize($userId, $name);
            if ($size) {
                $sizeIds[] = $size->id;
            }
        }

        $colourIds = [];
        foreach ($colourNames as $name) {
            $colour = $this->getColour($userId, $name);
            if ($colour) {
                $colourIds[] = $colour->id;
            }
        }

        $product = new Product([
            'user_id'        => $userId,
            'category_id'    => $category->id,
            'sub_category_id'=> $subCategory->id,
            'name'           => Str::ucfirst(trim($row['name'])),
            'description'    => $row['description'] ?? null,
            'code'           => $row['code'],
            'hsn_code'       => $row['hsn_code'] ?? null,
            'price'          => $price,
            'tax_amount'     => $taxAmount,
            'tax_id'         => $tax->id,
            'metric_id'      => $metric->id,
            'discount_type'  => $discountType,
            'discount'       => $row['discount'] ?? null,
            'quantity'       => $row['quantity'] ?? 0,
            // ✅ ADD THESE
            'is_size_differentiation_available'   => $isSize,
            'is_colour_differentiation_available' => $isColour,
            'size_id'   => !empty($sizeIds) ? implode(',', $sizeIds) : null,
            'colour_id' => !empty($colourIds) ? implode(',', $colourIds) : null,
            'is_active'      => 1,
            'is_bulk_upload' => 1,
            'run_id'         => $this->runId,
        ]);

        $product->save();

        $stock = Stock::create([
            'shop_id'        => $userId,
            'category_id'    => $category->id,
            'sub_category_id'=> $subCategory->id,
            'product_id'     => $product->id,
            'quantity'       => $row['quantity'] ?? 0,
            'is_active'      => 1,
        ]);

        // ---------------------------------------------
        // CREATE STOCK VARIATIONS FROM EXCEL
        // ---------------------------------------------

        $isSize   = strtolower(trim($row['is_size_differentiation_available'] ?? 'no')) === 'yes';
        $isColour = strtolower(trim($row['is_colour_differentiation_available'] ?? 'no')) === 'yes';

        // Convert comma-separated values to arrays
        $sizeNames   = !empty($row['size']) ? array_map('trim', explode(',', $row['size'])) : [];
        $colourNames = !empty($row['colour']) ? array_map('trim', explode(',', $row['colour'])) : [];

        // Convert to IDs (only valid ones)
        $sizes = [];
        foreach ($sizeNames as $name) {
            $size = $this->getSize($userId, $name);
            if ($size) {
                $sizes[] = $size->id;
            }
        }

        $colours = [];
        foreach ($colourNames as $name) {
            $colour = $this->getColour($userId, $name);
            if ($colour) {
                $colours[] = $colour->id;
            }
        }

        // CASE 1: Only sizes
        if ($isSize && !$isColour) {

            foreach ($sizes as $sizeId) {
                StockVariation::create([
                    'stock_id'   => $stock->id,
                    'product_id' => $product->id,
                    'size_id'    => $sizeId,
                    'quantity'   => 0,
                    'price'      => 0,
                ]);
            }
        }

        // CASE 2: Only colours
        elseif (!$isSize && $isColour) {

            foreach ($colours as $colourId) {
                StockVariation::create([
                    'stock_id'   => $stock->id,
                    'product_id' => $product->id,
                    'colour_id'  => $colourId,
                    'quantity'   => 0,
                    'price'      => 0,
                ]);
            }
        }

        // CASE 3: Both
        elseif ($isSize && $isColour) {

            foreach ($sizes as $sizeId) {
                foreach ($colours as $colourId) {

                    StockVariation::create([
                        'stock_id'   => $stock->id,
                        'product_id' => $product->id,
                        'size_id'    => $sizeId,
                        'colour_id'  => $colourId,
                        'quantity'   => 0,
                        'price'      => 0,
                    ]);
                }
            }
        }

        // CASE 4: None
        else {
            StockVariation::create([
                'stock_id'   => $stock->id,
                'product_id' => $product->id,
                'quantity'   => 0,
                'price'      => 0,
            ]);
        }

        return $product;
    }

    public function rules(): array
    {
        $userId = Auth::id();
        $row = $this->currentRow;

        return [
            '*.name' => ['required','string','max:100',
                function ($attribute, $value, $fail) use ($row, $userId) {
                    $categoryName    = trim($row['category'] ?? '');
                    $subCategoryName = trim($row['sub_category'] ?? '');

                    $category = $this->getCategory($userId, $categoryName);
                    if (!$category) return;

                    $subCategory = $this->getSubCategory($userId, $category->id, $subCategoryName);
                    if (!$subCategory) return;

                    $exists = Product::where('user_id', $userId)
                        ->where('category_id', $category->id)
                        ->where('sub_category_id', $subCategory->id)
                        ->whereRaw('LOWER(name)=?', [strtolower(trim($value))])
                        ->exists();

                    if ($exists) {
                        $fail("You already have a product '{$value}' in sub category '{$subCategoryName}' under '{$categoryName}'.");
                    }
                },
            ],

            '*.category' => ['required','string','max:50',
                function ($attribute, $value, $fail) use ($userId) {
                    if (!$this->getCategory($userId, $value)) {
                        $fail("Category '{$value}' does not exist.");
                    }
                },
            ],

            '*.sub_category' => ['required','string','max:50',
                function ($attribute, $value, $fail) use ($row, $userId) {
                    $categoryName = trim($row['category'] ?? '');
                    $category = $this->getCategory($userId, $categoryName);
                    if (!$category) return;

                    if (!$this->getSubCategory($userId, $category->id, $value)) {
                        $fail("Sub category '{$value}' does not exist in category '{$categoryName}'.");
                    }
                },
            ],

            '*.code' => ['required','max:50', Rule::unique('products','code')->where(fn($q) => $q->where('user_id',$userId))],
            '*.hsn_code' => ['nullable','max:50'],
            '*.price' => ['required','numeric','min:0'],
            '*.tax' => ['required', function($attr,$val,$fail) use($userId){
                if (!$this->getTax($userId, $val)) $fail("Tax '{$val}' is not valid.");
            }],
            '*.metric' => ['required', function($attr,$val,$fail) use($userId){
                if (!$this->getMetric($userId, $val)) $fail("Metric '{$val}' is not valid.");
            }],

            '*.size' => [
                'nullable',
                function ($attr, $value, $fail) use ($userId) {
                    $sizes = array_map('trim', explode(',', $value));
                    foreach ($sizes as $size) {
                        if (!$this->getSize($userId, $size)) {
                            $fail("Size '{$size}' not found.");
                        }
                    }
                }
            ],

            '*.colour' => [
                'nullable',
                function ($attr, $value, $fail) use ($userId) {
                    $colours = array_map('trim', explode(',', $value));
                    foreach ($colours as $colour) {
                        if (!$this->getColour($userId, $colour)) {
                            $fail("Colour '{$colour}' not found.");
                        }
                    }
                }
            ],

        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.name.required'  => 'Product Name is required.',
            '*.code.required'  => 'Product Code is required.',
            '*.price.required' => 'Price is required.',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
