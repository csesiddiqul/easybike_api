<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribution extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;


    public function distributionMedicine()
    {
        return $this->hasMany(DistributionMedicine::class);
    }

    // patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    // created by
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
}
