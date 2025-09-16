<?php

namespace App\Imports;

use App\Models\Classe;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class StudentImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            $department = Department::where('title', $row['department'])->first();
            $level = Level::where('title', $row['level'])->first();
            $section = Section::where('title', $row['section'])->first();

            // Determine the class ID based on department, level, and section
            $classeQuery = Classe::where('department_id', $department->id)
                ->where('level_id', $level->id);

            // Cache the class query result for reuse
            $classe = $classeQuery->when(
                $classeQuery->distinct()->count('section_id') > 1, // Check for multiple sections
                function ($query) use ($section) {
                    return $query->where('section_id', $section->id);
                }
            )->first();

            // Ensure $classe is not null before proceeding
if (!$classe) {
    // Handle the case where the class is not found
    continue; // or throw an exception or log an error
}

            
            Student::create(
                [
                    'classe_id'     => $classe->id,
                    'name'           => $row['name'],
                    'student_id'     => $row['student_id'],
                    'email'          => $row['email'],
                    'index_number'   => $row['index_number'],
                    'password'       => Hash::make($row['student_id']),
                    'email_verified_at' => Carbon::now()
                ]
            );
        }
    }

    public function prepareForValidation($data)
{
    $department = Department::where('title', $data['department'])->first();
    $level = Level::where('title', $data['level'])->first();
    $section = Section::where('title', $data['section'])->first();

    // Determine the class ID based on department, level, and section
    $classeQuery = Classe::where('department_id', $department->id)
        ->where('level_id', $level->id);

    // Cache the class query result for reuse
    $classe = $classeQuery->when(
        $classeQuery->distinct()->count('section_id') > 1, // Check for multiple sections
        function ($query) use ($section) {
            return $query->where('section_id', $section->id);
        }
    )->first();

    // Check if $classe is null before accessing the section property
    $data['class_has_many_section'] = $classe ? $classe->section->count() > 1 : false;

    // Ensure the class ID is set correctly if $classe is not null
    $data['classe'] = $classe ? $classe->id : null;
    return $data;
}


public function rules(): array
{
    return [
        '*.name' => 'required|string|max:100',
        '*.student_id' => 'required|unique:students,student_id|string|max:50',
        '*.email' => 'required|string|email|max:100|unique:students,email',
        '*.index_number' => 'required|string|max:50|unique:students,index_number',
        '*.department' => [
            'required',
            Rule::exists('departments', 'title'),
        ],
        '*.level' => [
            'required',
            Rule::exists('levels', 'title'),
        ],
        '*.section' => [
            'required_if:class_has_many_section,true',
            Rule::exists('sections', 'title'),
        ],
        '*.classe' => [
            Rule::exists('classes', 'id'),
        ],
    ];
}




    public function customValidationMessages()
    {
        return [
            '*.student_id.required' => 'The student ID field is required.',
            '*.student_id.unique' => 'The student ID has already been taken.',
            '*.index_number.required' => 'The index number field is required.',
            '*.index_number.unique' => 'The index number has already been taken.',
            '*.section.required_if' => 'The section field is required.',
            '*.classe.exists' => 'No class found for the given department, level and section.',
        ];
    }
}
