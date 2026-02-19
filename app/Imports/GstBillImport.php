<?php

namespace App\Imports;

use App\Models\GstBill;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Carbon\Carbon;

class GstBillImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsEmptyRows
{
    use SkipsFailures;

    private int $rowCount = 0;
    private int $runId;

    public function __construct(int $runId)
    {
        $this->runId = $runId;
    }

    public function model(array $row)
    {
        $category = Category::where('name', trim($row['category']))
            ->where('user_id', Auth::user()->parent_id)
            ->first();

        $subCategory = SubCategory::where('name', trim($row['sub_category']))
            ->where('category_id', $category?->id)
            ->where('user_id', Auth::user()->parent_id)
            ->first();

        $product = Product::where('name', trim($row['product']))
            ->where('sub_category_id', $subCategory?->id)
            ->where('user_id', Auth::user()->parent_id)
            ->first();

        $transactionDate = null;

        if (!empty($row['transaction_on'])) {
            $transactionDate = is_numeric($row['transaction_on'])
                ? Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['transaction_on'])
                )
                : Carbon::parse($row['transaction_on']);
        }

        ++$this->rowCount;

        return new GstBill([
            'shop_id'          => Auth::user()->parent_id,
            'branch_id'        => Auth::id(),
            'order_id'         => trim($row['order_id']),
            'reference_no'     => trim($row['reference_number']),
            'transfer_on'      => $transactionDate,
            'issued_by'        => trim($row['issued_by']),
            'sold_by'          => trim($row['sold_by']),
            'customer_name'    => trim($row['customer_name']),
            'customer_phone'   => trim($row['customer_mobile']),
            'customer_address' => trim($row['customer_address']),
            'category'         => $category?->id,
            'sub_category'     => $subCategory?->id,
            'product'          => $product?->id,
            'imie'             => trim($row['imei']),
            'item_code'        => trim($row['item_code']),
            'quantity'         => (int) $row['quantity'],
            'gross'            => str_replace(',', '', $row['gross']),
            'run_id'           => $this->runId,
        ]);
    }

    public function rules(): array
    {
        return [
            'order_id'      => 'required',
            'reference_number'  => 'required',
            'transaction_on'  => 'required',
            'issued_by'        => 'required|string|max:50',
            'sold_by'          => 'required|string|max:50',

            'customer_name'    => 'required|string|max:50',

            'customer_mobile'   => ['required','digits:10'],

            'customer_address' => 'required|string|max:200',
            'category'      => 'required|exists:categories,name',
            'sub_category'  => 'required|exists:sub_categories,name',
            'product'       => 'required|exists:products,name',
            'quantity'      => 'required|numeric|min:1',
            'gross'         => 'required|numeric',
            'imie'             => 'nullable|string|max:50',
            'item_code'        => 'required|string|max:50',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'order_id.required' => 'Order ID is required.',
            'category.exists'   => 'Category does not exist.',
            'quantity.numeric'  => 'Quantity must be numeric.',
            'gross.numeric'     => 'Gross must be numeric.',
        ];
    }

    public function getSuccessCount(): int
    {
        return $this->rowCount;
    }
}
