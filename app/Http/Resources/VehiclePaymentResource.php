<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclePaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'owner_id'       => $this->owner_id,
            'created_by'     => $this->created_by,
            'total_amount'   => $this->total_amount,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'paid_at'        => $this->paid_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,

            // 👤 Owner
            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id'                         => $this->owner->id,
                    'user_id'                    => $this->owner->user_id,
                    'father_or_husband_name'     => $this->owner->father_or_husband_name,
                    'ward_number'                => $this->owner->ward_number,
                    'mohalla_name'               => $this->owner->mohalla_name,
                    'nid_number'                 => $this->owner->nid_number,
                    'birth_registration_number' => $this->owner->birth_registration_number,
                    'present_address'            => $this->owner->present_address,
                    'permanent_address'          => $this->owner->permanent_address,
                    'image'                      => $this->owner->image,
                    'created_at'                 => $this->owner->created_at,
                    'updated_at'                 => $this->owner->updated_at,
                ];
            }),

            // 📦 Payment Items
            'items' => VehiclePaymentItemResource::collection(
                $this->whenLoaded('items')
            ),
        ];
    }
}
