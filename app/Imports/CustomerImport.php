<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\User;
use App\Models\Gender;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    private int $rowCount = 0;
    private int $runId;
    protected int $parentId;
    protected array $validGenders;

    public function __construct(int $runId)
    {
        $this->runId = $runId;

        $parent = User::find(Auth::id());
        $this->parentId = $parent->parent_id ?? Auth::id();

        // Fetch active genders
        $this->validGenders = Gender::where('is_active', 1)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])
            ->toArray();
    }

    public function model(array $row)
    {
        ++$this->rowCount;

        $name     = Str::ucfirst(trim($row['name'] ?? ''));
        $phone    = trim($row['phone'] ?? '');
        $altPhone = trim($row['alternate_phone'] ?? null);
        $address  = trim($row['address'] ?? '');
        $pincode  = trim($row['pincode'] ?? null);
        $gender   = trim($row['gender'] ?? null);
        $gst   = trim($row['gst'] ?? null);
        $dobExcel = $row['dob'] ?? null;
        $dob      = null;

        if ($dobExcel !== null) {
            $dobObj = Date::excelToDateTimeObject($dobExcel);
            $dob = $dobObj->format('Y-m-d');
        }

        // Map gender
        $genderId = null;
        if (!empty($gender)) {
            $key = strtolower($gender);
            if (isset($this->validGenders[$key])) {
                $genderId = $this->validGenders[$key];
            } else {
                return null; // invalid gender
            }
        }

        if (!empty($altPhone) && $altPhone === $phone) {
            return null; // skip invalid row
        }

        return new Customer([
            'user_id'         => $this->parentId,
            'branch_id'       => Auth::user()->id,
            'name'            => $name,
            'phone'           => $phone,
            'alt_phone'       => $altPhone,
            'address'         => $address,
            'pincode'         => $pincode,
            'gender_id'       => $genderId,
            'dob'             => $dob,
            'gst'             => $gst,
            'is_bulk_upload'  => 1,           // track bulk upload
            'run_id'          => $this->runId, // associate with run_id
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:50'],
            '*.phone' => [
                'required',
                'digits:10',
                function ($attribute, $value, $fail) {
                    $exists = Customer::where('user_id', $this->parentId)
                        ->where('phone', $value)
                        ->exists();
                    if ($exists) {
                        $fail("Phone '{$value}' is already used by another customer.");
                    }
                }
            ],
            '*.alternate_phone' => [
                'nullable',
                'digits:10',
                function ($attribute, $value, $fail) {
                    $phone = trim(request()->input(str_replace('.alternate_phone', '.phone', $attribute)) ?? '');
                    if ($value && $value === $phone) {
                        $fail('Alternate Phone must be different from Phone.');
                    }
                }
            ],
            '*.address' => ['required', 'string', 'max:200'],
            '*.pincode' => ['nullable', 'digits:6', 'regex:/^[1-9][0-9]{5}$/'],
            '*.gst' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            '*.dob' => [
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        try {
                            $date = Date::excelToDateTimeObject($value);
                            if ($date > now()) {
                                $fail('DOB cannot be a future date.');
                            }
                        } catch (\Exception $e) {
                            $fail('DOB must be a valid date.');
                        }
                    }
                }
            ],
            '*.gender' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !isset($this->validGenders[strtolower($value)])) {
                        $fail("Gender '{$value}' is invalid.");
                    }
                }
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required'            => 'Name is required.',
            'phone.required'           => 'Phone is required.',
            'phone.digits'             => 'Phone must be exactly 10 digits.',
            'alternate_phone.digits'   => 'Alternate Phone must be exactly 10 digits.',
            'address.required'         => 'Address is required.',
            'pincode.digits'           => 'Pincode must be exactly 6 digits.',
            'pincode.regex'            => 'Pincode is invalid.',
            'gst.regex'                => 'GST is invalid.',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
