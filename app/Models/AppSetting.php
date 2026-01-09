<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'system_name',
        'system_logo',
        'city_corporation_name',
        'city_corporation_logo',
        'city_corporation_phone',
        'vehicle_charge_per_year',
        'driver_licence_renew_charge',
    ];
}
