<?php

namespace App\Http\Requests;

use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
            // Owner
            'owner_id' => 'required|exists:owners,id',

            // Vehicle info
            'vehicle_type' => 'required|string|max:50',
            'supplier_type' => 'required',
            'vehicle_model_name' => 'required|string|max:100',
            'chassis_number' => 'required|string|max:100|unique:vehicles,chassis_number',

            // Driver (initial assignment)
            'driver_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // 1️⃣ Check in users table
                    $user = \App\Models\User::find($value);
                    if (! $user) {
                        return $fail('The selected user does not exist.');
                    }
                    if ($user->status !== 'Active') {
                        return $fail('The user account is not active.');
                    }

                    // 2️⃣ Check in drivers table
                    $driver = \App\Models\Driver::where('user_id', $value)->first();
                    if (! $driver) {
                        return $fail('The selected driver record does not exist.');
                    }
                    if ($driver->status !== 'active') {
                        return $fail('The driver is not active.');
                    }

                    // 3️⃣ Check in vehicle_driver_assignments table
                    $activeAssignment = \App\Models\VehicleDriverAssignment::where('driver_id', $value)
                        ->where('status', 'active')
                        ->exists();
                    if ($activeAssignment) {
                        return $fail('This driver is already assigned to another vehicle.');
                    }
                }
            ],

            // Status
            'status' => 'required|in:pending,approved,expired',
        ];
    }
}
