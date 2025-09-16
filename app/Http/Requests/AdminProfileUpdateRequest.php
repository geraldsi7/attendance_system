<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = Auth::guard('admin')->user();

        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique(Admin::class)->ignore($user->id)],  
              ];
    }
}