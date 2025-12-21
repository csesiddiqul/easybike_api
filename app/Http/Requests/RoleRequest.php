<?php

namespace App\Http\Requests;

use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'name' => 'required|string|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }
}
