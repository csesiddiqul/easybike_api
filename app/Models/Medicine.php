<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $guarded;

    public function category(){
        return $this->belongsTo(Category::class, "category_id");
    }
    public function medicine_unit(){
        return $this->belongsTo(MedicineUnit::class, "medicine_unit_id");
    }

    // local scope for active medicine 
    public function scopeActive($query){
        return $query->where('status', StatusEnum::Active->value);
    }
}
