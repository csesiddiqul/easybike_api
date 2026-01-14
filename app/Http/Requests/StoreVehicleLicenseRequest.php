<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Vehicle;

class StoreVehicleLicenseRequest extends FormRequest
{
    public function authorize()
    {
        return true; // আপনি চাইলে এখানে user permissions check করতে পারেন
    }

    public function rules()
    {
        return [
            'owner_id' => ['required', 'exists:owners,id'],

            'vehicle_id' => [
                'required',
                'exists:vehicles,id',
                function ($attribute, $value, $fail) {
                    $ownerId = $this->input('owner_id');

                    $vehicle = Vehicle::find($value);

                    if (!$vehicle) {
                        return $fail("Selected vehicle does not exist.");
                    }

                    if ($vehicle->owner_id != $ownerId) {
                        return $fail("This vehicle does not belong to the selected owner.");
                    }
                }
            ],

            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'licence_fee'    => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'owner_id.required' => 'Owner is required.',
            'owner_id.exists'   => 'Selected owner does not exist.',
            'vehicle_id.required' => 'Vehicle is required.',
            'vehicle_id.exists'   => 'Selected vehicle does not exist.',
            'fiscal_year_id.required' => 'Fiscal year is required.',
            'fiscal_year_id.exists'   => 'Selected fiscal year does not exist.',
            'licence_fee.required' => 'Licence fee is required.',
            'licence_fee.numeric'  => 'Licence fee must be a number.',
            'licence_fee.min'      => 'Licence fee must be at least 0.',
        ];
    }
}
