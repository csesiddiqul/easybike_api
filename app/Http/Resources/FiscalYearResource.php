<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiscalYearResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,

            // 🔑 ONLY DATE (no timezone, no ISO)
            'start_date' => optional($this->start_date)->format('Y-m-d'),
            'end_date'   => optional($this->end_date)->format('Y-m-d'),

            'is_active'  => (bool) $this->is_active,
            'created_at' => optional($this->created_at)->format('Y-m-d'),
        ];
    }

}
