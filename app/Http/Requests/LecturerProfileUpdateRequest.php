<?php

namespace App\Http\Requests;

use App\Models\Lecturer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LecturerProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = Auth::guard('lecturer')->user();

        return [
            'name' => ['string', 'max:255'],
            'staff_id' => ['string', 'max:255', Rule::unique(Lecturer::class)->ignore($user->id)],
            'email' => ['email', 'max:255', Rule::unique(Lecturer::class)->ignore($user->id)],  
              ];
    }
}