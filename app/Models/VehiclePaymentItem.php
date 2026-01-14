<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePaymentItem extends Model
{
    protected $fillable = [
        'vehicle_payment_id',
        'licence_id',
        'amount',
    ];

    public function payment()
    {
        return $this->belongsTo(VehiclePayment::class, 'vehicle_payment_id');
    }

    public function licence()
    {
        return $this->belongsTo(VehicleLicense::class, 'licence_id');
    }
}
