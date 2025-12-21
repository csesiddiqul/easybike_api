<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Enums\StockStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;

    protected $casts = [
        /* ... */
        'status' => StatusEnum::class,
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function stockedByStock()
    {
        return $this->belongsTo(User::class, 'stocked_by');
    }
    public function medicine()
    {
    return $this->belongsTo(Medicine::class);
    }

    // local scope for active medicine 
    public function scopeActive($query, $status){
        return $query->when($status, function ($query) use ($status){
            return $query->where('status', $status);
        });
    }
}