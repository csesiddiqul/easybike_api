<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLicenceRegistration extends Model
{
    protected $fillable = [
        'driver_id',
        'fiscal_year_id',
        'start_date',
        'end_date',
        'payment_status',
        'approved_at',
        'approved_by',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'reference_id')
            ->where('type', 'driver_licence');
    }
}


