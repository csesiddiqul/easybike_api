<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules based on update() controller
     */
    public function rules(): array
    {
        $driver = $this->route('driver');

        return [

            /* =====================
               USER UPDATE
            ===================== */
            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($driver->user_id),
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($driver->user_id),
            ],

            /* =====================
               DRIVER PROFILE UPDATE
            ===================== */
            'nid' => [
                'required',
                'string',
                Rule::unique('drivers', 'nid')->ignore($driver->id),
            ],

            'years_of_experience' => ['required', 'integer', 'min:0', 'max:60'],

            'present_address'   => ['required', 'string'],
            'permanent_address' => ['required', 'string'],

            'driver_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number is already used by another driver.',
            'email.unique' => 'This email is already used by another driver.',
            'nid.unique'   => 'This NID is already registered.',
        ];
    }
}
