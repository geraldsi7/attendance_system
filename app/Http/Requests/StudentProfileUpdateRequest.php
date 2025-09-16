<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = Auth::guard('student')->user();

        return [
            'name' => ['string', 'max:255'],
            'student_id' => ['string', 'max:255', Rule::unique(Student::class)->ignore($user->id)],
            'email' => ['email', 'max:255', Rule::unique(Student::class)->ignore($user->id)],  
              ];
    }
}