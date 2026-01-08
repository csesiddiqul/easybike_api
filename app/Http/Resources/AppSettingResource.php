<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'system_name' => $this->system_name,
            'system_logo' => $this->system_logo
                ? url($this->system_logo)
                : null,

            'city_corporation_name' => $this->city_corporation_name,
            'city_corporation_logo' => $this->city_corporation_logo
                ? url($this->city_corporation_logo)
                : null,
            'city_corporation_phone' => $this->city_corporation_phone,

            'vehicle_charge_per_year' => $this->vehicle_charge_per_year,
            'driver_licence_renew_charge' => $this->driver_licence_renew_charge,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
