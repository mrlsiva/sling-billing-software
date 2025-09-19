<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    public function model(array $row)
    {
        return new Category([
            'user_id'   => Auth::id(),
            'name'      => Str::ucfirst(trim($row['name'])),
            'is_active' => 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'name')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique'   => 'You already have a category with this name.',
        ];
    }
}
