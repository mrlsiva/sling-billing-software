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
use Carbon\Carbon;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    protected $parentId;
    protected $validGenders;

    public function __construct()
    {
        $parent = User::find(Auth::id());
        $this->parentId = $parent->parent_id ?? Auth::id();

        // Fetch all active genders
        $this->validGenders = Gender::where('is_active', 1)->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();
    }

    public function model(array $row)
    {
        $name       = Str::ucfirst(trim($row['name'] ?? ''));
        $phone      = trim($row['phone'] ?? '');
        $altPhone   = trim($row['alternate_phone'] ?? null);
        $address    = trim($row['address'] ?? '');
        $pincode    = trim($row['pincode'] ?? null);
        $gender     = trim($row['gender'] ?? null);
        $dobExcel = $row['dob'] ?? null;
        $dob = null;

        if ($dobExcel != null) {
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

        if (!empty($altPhone)) 
        {
            if($phone === $altPhone) 
            {
                return null;
            }
        }

        return new Customer([
            'user_id'   => $this->parentId,
            'name'      => $name,
            'phone'     => $phone,
            'alt_phone' => $altPhone,
            'address'   => $address,
            'pincode'   => $pincode,
            'gender_id' => $genderId,
            'dob'       => $dob,
        ]);
    }


    public function rules(): array
    {
        $parentId = $this->parentId;
        $validGenderNames = array_keys($this->validGenders);

        return [
            '*.name' => ['required', 'string', 'max:50'],
            '*.phone' => [
                'required',
                'digits:10',
                function ($attribute, $value, $fail) use ($parentId, &$row) {
                    $exists = Customer::where('user_id', $parentId)
                        ->where('phone', $value)
                        ->exists();
                    if ($exists) {
                        $fail("Phone '{$value}' is already used by another customer.");
                    }

                    $altPhone = trim($row['alternate_phone'] ?? '');
                    if ($altPhone && $altPhone === $value) {
                        $fail('Phone and Alternate Phone must be different.');
                    }
                }
            ],
            '*.alternate_phone' => [
                'nullable',
                'digits:10',
                function ($attribute, $value, $fail) use (&$row) {
                    $phone = trim($row['phone'] ?? '');
                    if ($value && $value === $phone) {
                        $fail('Alternate Phone must be different from Phone.');
                    }
                }
            ],
            '*.address' => ['required', 'string', 'max:200'],
            '*.pincode' => ['nullable', 'digits:6', 'regex:/^[1-9][0-9]{5}$/'],
            '*.dob' => [
                function($attribute, $value, $fail) {
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
                function ($attribute, $value, $fail) use ($validGenderNames) {
                    if (!empty($value) && !in_array(strtolower($value), $validGenderNames)) {
                        $fail("Gender '{$value}' is invalid.");
                    }
                }
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone is required.',
            'phone.digits' => 'Phone must be exactly 10 digits.',
            'alternate_phone.digits' => 'Alternate Phone must be exactly 10 digits.',
            'address.required' => 'Address is required.',
            'pincode.digits' => 'Pincode must be exactly 6 digits.',
            'pincode.regex' => 'Pincode is invalid.',
            'dob.date' => 'DOB must be a valid date.',
        ];
    }
}
