<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SubCategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    protected $currentRow = [];
    private int $rowCount = 0;
    private int $runId;

    public function __construct(int $runId)
    {
        $this->runId = $runId;
    }

    public function prepareForValidation($data, $index)
    {
        $this->currentRow = $data;
        return $data;
    }

    public function model(array $row)
    {
        $categoryName    = trim($row['category'] ?? '');
        $subCategoryName = trim($row['name'] ?? '');

        $category = Category::where('user_id', Auth::id())
            ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
            ->first();

        // Increment row count even if skipped
        ++$this->rowCount;

        if (!$category) {
            return null;
        }

        $exists = SubCategory::where('user_id', Auth::id())
            ->where('category_id', $category->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($subCategoryName)])
            ->exists();

        if ($exists) {
            return null;
        }

        return new SubCategory([
            'user_id'     => Auth::id(),
            'category_id' => $category->id,
            'name'        => Str::ucfirst($subCategoryName),
            'is_active'   => 1,
            'is_bulk_upload' => 1,
            'run_id'      => $this->runId, // store run_id
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Category::where('user_id', Auth::id())
                        ->whereRaw('LOWER(name) = ?', [strtolower(trim($value))])
                        ->exists();
                    if (!$exists) {
                        $fail("Category '{$value}' does not exist.");
                    }
                },
            ],
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $categoryName = trim($this->currentRow['category'] ?? '');
                    $category = Category::where('user_id', Auth::id())
                        ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                        ->first();

                    if ($category) {
                        $exists = SubCategory::where('user_id', Auth::id())
                            ->where('category_id', $category->id)
                            ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                            ->exists();
                        if ($exists) {
                            $fail("You already have a sub category '{$value}' in category '{$categoryName}'.");
                        }
                    }
                },
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'category.required' => 'Category is required.',
            'name.required'     => 'Sub Category name is required.',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
