<?php

namespace App\Http\Requests;

use App\Models\Settings;
use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    use FailedValidation;
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
            'name' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'role_id' => 'required',
            'email' => 'required|string|email|max:120|unique:users',
        ];
    }
}
