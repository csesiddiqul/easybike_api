<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionMedicine extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;

    public function medicine(){
        return $this->belongsTo(Medicine::class);
    }
}
