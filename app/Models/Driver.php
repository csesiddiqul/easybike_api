<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $appends = ['computed_status'];
    protected $fillable = [
        'user_id',
        'registration_number',
        'driver_image',
        'nid',
        'registration_date',
        'years_of_experience',
        'present_address',
        'permanent_address',
        'status',
    ];

    /* =====================
       Relationships
    ===================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function licenceRegistrations()
    {
        return $this->hasMany(DriverLicenceRegistration::class);
    }


    public function latestLicence()
    {
        return $this->hasOne(
            DriverLicenceRegistration::class
        )->latestOfMany();
    }




    public function getComputedStatusAttribute()
    {
        $licence = $this->latestLicence;

        if (!$licence) {
            return 'pending';
        }

        if (
            $licence->payment_status === 'paid' &&
            $licence->end_date >= now()
        ) {
            return 'active';
        }

        if (
            $licence->payment_status === 'unpaid' &&
            $licence->end_date >= now()
        ) {
            return 'pending';
        }

        if (
            $licence->payment_status === 'unpaid' &&
            $licence->end_date === null
        ) {
            return 'pending';
        }


        return 'expired';
    }


    /* =====================
       Helper Method
    ===================== */

    public function hasValidLicence(): bool
    {
        return $this->licenceRegistrations()
            ->where('payment_status', 'paid')
            ->whereHas('fiscalYear', function ($q) {
                $q->where('end_date', '>=', now());
            })
            ->exists();
    }
}
