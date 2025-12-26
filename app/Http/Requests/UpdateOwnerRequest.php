<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnerRequest extends FormRequest
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
        // $id = $this->route('owner');
        return [
            'name'  => 'required|string|max:255',
            // 'phone' => 'required|string|max:20|unique:users,phone,' . $this->owner->user_id,

            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^(\+8801[3-9]\d{8}|01[3-9]\d{8})$/',

                function ($attribute, $value, $fail) {
                    $normalized = normalizePhone($value);

                    $owner = $this->route('owner');   // Owner model (route model binding)
                    $userId = $owner?->user_id;       // related users.id

                    $exists = User::where('phone', $normalized)
                        ->where('id', '!=', $userId)
                        ->exists();

                    if ($exists) {
                        $fail('Phone number is already registered.');
                    }
                },
            ],
            
            'status' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $this->owner->user_id,

            // Owner table
            'father_or_husband_name'      => 'required|string|max:255',
            'ward_number'                 => 'required|string|max:50',
            'mohalla_name'                => 'required|string|max:255',
            'nid_number'                  => 'nullable|string|max:30',
            'birth_registration_number'   => 'nullable|string|max:30',
            'present_address'             => 'required|string',
            'permanent_address'           => 'required|string',
            'image'                       => 'nullable|file|image',
        ];
    }
}
