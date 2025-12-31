<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;
    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function currentDriver()
    {
        return $this->hasOne(VehicleDriverAssignment::class, 'vehicle_id')
            ->where('status', 'active');
    }

    public static function generateRegistrationNumber(): string
    {
        $lastId = self::max('id') ?? 0;
        return 'AUTO-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
    }
}
