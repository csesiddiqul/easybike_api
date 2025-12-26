<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->role
            && auth()->user()->role->name === 'Super Admin';
    }


    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [

            /* ======================
               USER INFO
            ====================== */
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'unique:users,email'],

            /* ======================
               DRIVER PROFILE
            ====================== */
            'driver_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'nid' => ['required', 'string', 'unique:drivers,nid'],

            'years_of_experience' => ['required', 'integer', 'min:0', 'max:60'],

            'present_address'   => ['required', 'string'],
            'permanent_address' => ['required', 'string'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email address is already in use.',
            'nid.unique'   => 'This NID is already registered as a driver.',
        ];
    }
}
