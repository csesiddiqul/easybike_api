<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // প্রয়োজনে permission বসাতে পারো
    }

    public function rules(): array
    {
        return [
            'system_name' => 'nullable|string|max:255',
            'system_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',

            'city_corporation_name' => 'nullable|string|max:255',
            'city_corporation_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'city_corporation_phone' => 'nullable|string|max:50',

            'vehicle_charge_per_year' => 'nullable|numeric|min:0',
            'driver_licence_renew_charge' => 'nullable|numeric|min:0',
        ];
    }
}
