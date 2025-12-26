<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'medicine_id'           => isset($this->medicine_id) ? (int) $this->medicine_id : null,
            'medicine_name'         => $this->medicine_name ?? $this->name ?? null,

            // stock aggregates
            'previous_stock'        => (int) ($this->previous_stock ?? 0),
            'stock_between'         => (int) ($this->stock_between ?? 0),
            'total_stock'           => (int) ($this->total_stock ?? 0),
            'remaining_from_stocks' => (int) ($this->remaining_from_stocks ?? 0),

            // distribution aggregates
            'previous_distribution'    => (int) ($this->previous_distribution ?? 0),
            'distribution_between'     => (int) ($this->distribution_between ?? 0),
            'total_distribution'       => (int) ($this->total_distribution ?? 0),

            // calculated remaining (total_stock - total_distribution)
            'remaining_calculated'     => (int) ($this->remaining_calculated ?? ((int)($this->total_stock ?? 0) - (int)($this->total_distribution ?? 0)) ),

            // helpful flag
            'is_out_of_stock' => ((int) ($this->remaining_calculated ?? 0)) <= 0,
        ];
    }
}
