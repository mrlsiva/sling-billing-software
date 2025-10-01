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

    private int $rowCount = 0;
    private int $runId;

    // 👇 accept run_id in constructor
    public function __construct(int $runId)
    {
        $this->runId = $runId;
    }

    public function model(array $row)
    {
        ++$this->rowCount;

        return new Category([
            'user_id'        => Auth::id(),
            'name'           => Str::ucfirst(trim($row['name'])),
            'is_active'      => 1,
            'is_bulk_upload' => 1,
            'run_id'         => $this->runId, // 👈 store run_id
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

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
