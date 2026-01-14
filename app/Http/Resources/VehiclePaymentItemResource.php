<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclePaymentItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'vehicle_payment_id' => $this->vehicle_payment_id,
            'licence_id'         => $this->licence_id,
            'amount'             => $this->amount,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,

            // 🚗 Licence
            'licence' => $this->whenLoaded('licence', function () {
                return new LicenceResource($this->licence);
            }),
        ];
    }
}
