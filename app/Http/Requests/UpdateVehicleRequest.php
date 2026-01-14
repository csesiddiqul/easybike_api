<?php

namespace App\Http\Requests;

use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'owner_id' => 'required|exists:owners,id',
            'vehicle_type' => 'required|string|max:50',
            'supplier_type' => 'required',
            'vehicle_model_name' => 'required|string|max:100',
            'chassis_number' => 'required|string|max:100|unique:vehicles,chassis_number,' . $this->vehicle->id,
            'driver_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::with('driver')->find($value);
                    if (!$user) return $fail('The selected user does not exist.');
                    // if ($user->status !== 'active') return $fail('The user account is not active.');

                    $driver = \App\Models\Driver::where('user_id', $value)->first();
                    if (!$driver) return $fail('The selected driver record does not exist.');
                    // if ($driver->getComputedStatusAttribute() !== 'active') return $fail('The driver is not active.');
                    if ($driver->computed_status !== 'active') {
                        return $fail('The driver is not active.');
                    }


                    $activeAssignment = \App\Models\VehicleDriverAssignment::where('driver_id', $value)
                        ->where('status', 'active')
                        ->where('vehicle_id', '!=', $this->vehicle->id)
                        ->exists();
                    if ($activeAssignment) return $fail('This driver is already assigned to another vehicle.');
                }
            ],
            'status' => 'required|in:pending,approved,expired',
        ];
    }
}
