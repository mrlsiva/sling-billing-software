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

    public function model(array $row)
    {
        $userId = Auth::id();

        $category = Category::where('user_id', $userId)->whereRaw('LOWER(name) = ?', [strtolower(trim($row['category']))])->first();

        if (!$category) {
            return null; // Skipped
        }

        $subCategory = SubCategory::where('user_id', $userId)->where('category_id', $category->id)->whereRaw('LOWER(name) = ?', [strtolower(trim($row['sub_category']))])->first();

        if (!$subCategory) {
            return null; // Skipped
        }

        $tax = Tax::where('shop_id', $userId)->whereRaw('LOWER(name) = ?', [strtolower(trim($row['tax']))])->first();

        if (!$tax) {
            return null; // Skipped
        }

        $metric = Metric::where('shop_id', $userId)->whereRaw('LOWER(name) = ?', [strtolower(trim($row['metric']))])->first();

        if (!$metric) {
            return null; // Skipped
        }

        // Prevent duplicate product name in same category/sub-category
        $exists = Product::where('user_id', $userId)->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->whereRaw('LOWER(name) = ?', [strtolower(trim($row['name']))])->exists();

        if ($exists) {
            return null; // Skipped duplicate
        }

        // Map discount type to integer
        $discountType = null;
        if (!empty($row['discount_type'])) {
            $discountType = strtolower(trim($row['discount_type'])) === 'flat' ? 1 : 2;
        }

        // Calculate tax amount
        $price = (float) $row['price'];

        if ($tax) {
            $taxRate   = (float) $tax->name; 
            $taxAmount   = $price / (1 + ($taxRate / 100)); 
        } else {
            $taxAmount = 0;
        }

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
            'tax_id'         => $tax ? $tax->id : null,
            'metric_id'      => $metric ? $metric->id : null,
            'discount_type'  => $discountType,
            'discount'       => $row['discount'] ?? null,
            'quantity'       => $row['quantity'] ?? 0,
            'is_active'      => 1,
        ]);

        // After product is created, also create stock entry
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

        return [
            '*.name' => ['required','string','max:50',
                function ($attribute, $value, $fail) {
                    $rowIndex = explode('.', $attribute)[0]; // e.g. "3.name" → "3"
                    $rowData = request()->all()[$rowIndex] ?? null;

                    if (!$rowData) return;

                    $userId = Auth::id();
                    $categoryName = trim($rowData['category'] ?? '');
                    $subCategoryName = trim($rowData['sub_category'] ?? '');

                    // Find category
                    $category = Category::where('user_id', $userId)->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();

                    if (!$category) return;

                    // Find subcategory
                    $subCategory = SubCategory::where('user_id', $userId)->where('category_id', $category->id)->whereRaw('LOWER(name) = ?', [strtolower($subCategoryName)])->first();

                    if (!$subCategory) return;

                    // Check if product already exists
                    $exists = Product::where('user_id', $userId)->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->whereRaw('LOWER(name) = ?', [strtolower(trim($value))])->exists();

                    if ($exists) {
                        $fail("You already have a product '{$value}' in sub category '{$subCategoryName}' under '{$categoryName}'.");
                    }
                },
            ],

            '*.category' => ['required','string','max:50',
                function ($attribute, $value, $fail) use ($userId) {
                    $exists = Category::where('user_id', $userId)->whereRaw('LOWER(name) = ?', [strtolower(trim($value))])->exists();

                    if (!$exists) {
                        $fail("Category '{$value}' does not exist.");
                    }
                },
            ],

            '*.sub_category' => ['required','string','max:50',
                function ($attribute, $value, $fail) use ($userId) {
                    // Find the category from the row
                    $rowIndex = explode('.', $attribute)[0]; // e.g. "3.sub_category" → "3"
                    $rowData = request()->all()[$rowIndex] ?? null;

                    $categoryName = $rowData['category'] ?? null;
                    if (!$categoryName) return;

                    $category = Category::where('user_id', $userId)->whereRaw('LOWER(name) = ?', [strtolower(trim($categoryName))])->first();

                    if (!$category) return;

                    $exists = SubCategory::where('user_id', $userId)->where('category_id', $category->id)->whereRaw('LOWER(name) = ?', [strtolower(trim($value))])->exists();

                    if (!$exists) {
                        $fail("Sub category '{$value}' does not exist in category '{$categoryName}'.");
                    }
                },
            ],

            '*.code' => ['required','max:50',
                Rule::unique('products', 'code')->where(fn($q) => $q->where('user_id', $userId)),
            ],

            '*.hsn_code' => ['nullable','max:50',
                Rule::unique('products', 'hsn_code')->where(fn($q) => $q->where('user_id', $userId)),
            ],

            '*.price' => ['required', 'numeric', 'min:1'],

            '*.tax' => ['required',
                function ($attribute, $value, $fail) use ($userId) {
                    $exists = Tax::where('shop_id', $userId)->where('is_active', 1)->whereRaw('LOWER(name) = ?', [strtolower(trim($value))])->exists();

                    if (!$exists) {
                        $fail("Tax '{$value}' is not valid.");
                    }
                },
            ],

            '*.metric' => ['required',
                function ($attribute, $value, $fail) use ($userId) {
                    $exists = Metric::where('shop_id', $userId)->where('is_active', 1)->whereRaw('LOWER(name) = ?', [strtolower(trim($value))])->exists();

                    if (!$exists) {
                        $fail("Metric '{$value}' is not valid.");
                    }
                },
            ],

            '*.discount_type' => [
                function ($attribute, $value, $fail) use (&$row) {
                    $discount = $row['discount'] ?? null;

                    if ($discount && empty($value)) {
                        $fail('Discount Type is required when Discount is provided.');
                    }

                    if (!empty($value) && !in_array(strtolower($value), ['flat','percentage'])) {
                        $fail('Discount Type must be either Flat or Percentage.');
                    }
                },
            ],

            '*.discount' => [
                function ($attribute, $value, $fail) use (&$row) {
                    $discountType = $row['discount_type'] ?? null;

                    if ($discountType && ($value === null || $value === '')) {
                        $fail('Discount value is required when Discount Type is provided.');
                    }

                    if ($value !== null && !is_numeric($value)) {
                        $fail('Discount must be numeric.');
                    }

                    if ($value < 0) {
                        $fail('Discount cannot be negative.');
                    }
                },
            ],


            'quantity' => ['numeric','min:0'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.name.required' => 'Product Name is required.',
            '*.code.required' => 'Product Code is required.',
            '*.price.required'=> 'Price is required.',
        ];
    }
}
