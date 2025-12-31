<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'registration_number'   => $this->registration_number,
            'status' => $this->computed_status,
            'years_of_experience'   => $this->years_of_experience,
            'registration_date'     => $this->registration_date,

            'driver_image' => $this->driver_image
                ? url('storage/' . $this->driver_image)
                : null,

            /* =====================
               USER INFO
            ===================== */
            'user' => [
                'id'    => $this->user?->id,
                'name'  => $this->user?->name,
                'phone' => $this->user?->phone,
                'email' => $this->user?->email,
            ],

            /* =====================
               ADDRESS
            ===================== */
            'nid'               =>$this->nid,
            'present_address'   => $this->present_address,
            'permanent_address' => $this->permanent_address,


            'latest_licence' => $this->latestLicence ? [
                'id' => $this->latestLicence->id,
                'fiscal_year' => $this->latestLicence->fiscalYear?->name,
                'start_date' => $this->latestLicence->start_date,
                'end_date' => $this->latestLicence->end_date,
                'payment_status' => $this->latestLicence->payment_status,
            ] : null,

            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d'),
        ];
    }
}
