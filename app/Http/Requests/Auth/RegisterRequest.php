<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|max:250',
            'email' => 'required|unique:users,email|email',
            'password' => 'required|min:8',
            'phone' => ['required', 'numeric', 'digits:11', 'starts_with:011,015,010,012,018', 'unique:users,phone'],
        ];
    }
}
