<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleLicense extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleLicenseFactory> */
    use HasFactory;

    protected $table = 'vehicle_licenses';

    // Mass assignable fields
    protected $fillable = [
        'owner_id',
        'vehicle_id',
        'fiscal_year_id',
        'licence_fee',
        'status',          // pending | active | expired
        'payment_status',  // unpaid | paid
        'activated_at',
        'expired_at',
    ];

    // Cast dates
    protected $casts = [
        'activated_at' => 'datetime',
        'expired_at'   => 'datetime',
    ];

    /**
     * Relations
     */

    // Owner relation
    public function owner()
    {
        return $this->belongsTo(Owner::class,'owner_id');
    }

    // Vehicle relation
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Fiscal Year relation
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Check if licence is payable
     */
    public function getIsPayableAttribute()
    {
        return in_array($this->status, ['pending', 'expired']) && $this->payment_status === 'unpaid';
    }
}
