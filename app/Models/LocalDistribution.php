<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocalDistribution extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'patient_id',
        'receiver_type',
        'patient_name',
        'prescription_code',
        'notes',
        'total_price',
        'created_by'
    ];


    public function distributionMedicine()
    {
        return $this->hasMany(LocalDistributionMedicine::class, 'distribution_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
