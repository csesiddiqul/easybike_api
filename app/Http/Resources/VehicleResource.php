<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'vehicle_type' => $this->vehicle_type,
            'supplier_type' => $this->supplier_type,
            'registration_number' => $this->registration_number,
            'vehicle_model_name' => $this->vehicle_model_name,
            'chassis_number' => $this->chassis_number,
            'status' => $this->status,
            'current_driver' => $this->currentDriver ? [
                'id' => $this->currentDriver->id,
                'vehicle_id' => $this->currentDriver->vehicle_id,
                'driver_id' => $this->currentDriver->driver_id,
                'start_date' => $this->currentDriver->start_date,
                'end_date' => $this->currentDriver->end_date,
                'status' => $this->currentDriver->status,
                'driver' => $this->currentDriver->driver ? [
                    'id' => $this->currentDriver->driver->id,
                    'name' => $this->currentDriver->driver->name,
                    'email' => $this->currentDriver->driver->email,
                    'phone' => $this->currentDriver->driver->phone,
                    'user_name' => $this->currentDriver->driver->user_name,
                    'role_id' => $this->currentDriver->driver->role_id,
                    'status' => $this->currentDriver->driver->status,
                ] : null,
            ] : null,
            'owner' => $this->owner->user ? [
                'id' => $this->owner->user->id,
                'name' => $this->owner->user->name,
                'email' => $this->owner->user->email,
                'phone' => $this->owner->user->phone,
                'user_name' => $this->owner->user->user_name,
                'role_id' => $this->owner->user->role_id,
                'status' => $this->owner->user->status,
            ] : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
