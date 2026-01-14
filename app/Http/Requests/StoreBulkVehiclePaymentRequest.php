<?php

namespace App\Http\Requests;

use App\Models\VehicleLicense;
use App\Validation\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkVehiclePaymentRequest extends FormRequest
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
            'owner_id'        => ['required', 'exists:owners,id'],
            'payment_method' => ['required', 'in:cash,ssl'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.licence_id' => ['required', 'exists:vehicle_licenses,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $ownerId = $this->owner_id;

            foreach ($this->items as $item) {
                $licence = VehicleLicense::with('vehicle')
                    ->find($item['licence_id']);

                if (!$licence) {
                    continue;
                }

                if ($licence->vehicle->owner_id != $ownerId) {
                    $validator->errors()->add(
                        'licence_id',
                        "Licence ID {$licence->id} does not belong to this owner."
                    );
                }

                if ($licence->payment_status === 'paid') {
                    $validator->errors()->add(
                        'licence_id',
                        "Licence ID {$licence->id} already paid."
                    );
                }
            }
        });
    }
}
