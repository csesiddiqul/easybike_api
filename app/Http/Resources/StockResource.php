<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'warehouse' => new WarehouseResource($this->whenLoaded("warehouse")),
            'stocked_by' => new UserResource($this->whenLoaded("stockedByStock")),
            'medicine' => new MedicineResource($this->whenLoaded("medicine")),
            'quantity' => $this->quantity,
            'total_quantity' => $this->total_quantity,
            'expiry_date' => $this->expiry_date, 
            'created_at' => $this->created_at, 
        ];
    }
}
