<?php

namespace App\Imports;

use App\Models\Lecturer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LecturerImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            Lecturer::create(
                [
                    'name'           => $row['name'],
                    'staff_id'       => $row['staff_id'],
                    'email'          => $row['email'],
                    'password'       => Hash::make($row['staff_id']),
                    'email_verified_at' => Carbon::now()
                ]
            );
        }
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required',
            '*.staff_id' => 'required | unique:lecturers',
            '*.email' => 'required | email | unique:lecturers'
        ];
    }


    public function customValidationMessages()
    {
        return [
            '*.staff_id.required' => 'The staff ID field of is required.',
            '*.staff_id.unique' => 'The staff ID has already been taken.',
        ];
    }
}
