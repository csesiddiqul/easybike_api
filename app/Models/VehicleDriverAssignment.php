<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDriverAssignment extends Model
{
    //
    protected $guarded = [];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
