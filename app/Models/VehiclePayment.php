<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePayment extends Model
{
    protected $fillable = [
        'owner_id',
        'total_amount',
        'payment_status',
        'payment_method',
        'transaction_id',
        'created_by',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(VehiclePaymentItem::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
