<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'fiscal_year_id',
        'amount',
        'payment_method',
        'trx_id',
        'status',
        'paid_at',
    ];

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function licence()
    {
        return $this->belongsTo(
            DriverLicenceRegistration::class,
            'reference_id',
            'id'
        );
    }

}

