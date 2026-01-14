<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenceResource extends JsonResource
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
            'vehicle_id'     => $this->vehicle_id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'licence_fee'    => $this->licence_fee,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,
            'activated_at'   => $this->activated_at,
            'expired_at'     => $this->expired_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
