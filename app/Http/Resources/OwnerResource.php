<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
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
            'user_id' => $this->user_id,

            'user' => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'status' => $this->user->status,
            ],

            'father_or_husband_name' => $this->father_or_husband_name,
            'ward_number' => $this->ward_number,
            'mohalla_name' => $this->mohalla_name,
            'nid_number' => $this->nid_number,
            'birth_registration_number' => $this->birth_registration_number,
            'present_address' => $this->present_address,
            'permanent_address' => $this->permanent_address,
            'image' => asset('storage/' . $this->image) ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
