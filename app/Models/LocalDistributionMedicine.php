<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalDistributionMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'medicine_name',
        'quantity',
        'price',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    public function distribution()
    {
        return $this->belongsTo(LocalDistribution::class, 'distribution_id', 'id');
    }
}
