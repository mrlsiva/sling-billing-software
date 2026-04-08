<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\Metric;
use App\Models\Stock;
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
            'is_active'      => 1,
            'is_bulk_upload' => 1,
            'run_id'         => $this->runId,
        ]);

        $product->save();

        Stock::create([
            'shop_id'        => $userId,
            'category_id'    => $category->id,
            'sub_category_id'=> $subCategory->id,
            'product_id'     => $product->id,
            'quantity'       => $row['quantity'] ?? 0,
            'is_active'      => 1,
        ]);

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
