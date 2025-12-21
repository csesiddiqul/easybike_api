<?php

namespace App\Models;

use App\Enums\EmploymentEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $guarded;

    public function spouses()
    {
        return $this->hasMany(Spouse::class, "patient_id");
    }

    public function childrens()
    {
        return $this->hasMany(Children::class, "patient_id");
    }


    public function police_unit(){
        return $this->belongsTo(PoliceUnit::class, "police_unit_id");
    }
    
    public function designation(){
        return $this->belongsTo(Designation::class, "designation_id");
    }

     // Define the relationships for present addresses
     public function presentDivision()
     {
         return $this->belongsTo(Division::class, 'present_division_id');
     }
 
     public function presentDistrict()
     {
         return $this->belongsTo(District::class, 'present_district_id');
     }
 
     public function presentUpazila()
     {
         return $this->belongsTo(Upazila::class, 'present_upazila_id');
     }
 
     public function presentUnion()
     {
         return $this->belongsTo(Union::class, 'present_union_id');
     }
 
     // Define the relationships for permanent addresses
     public function permanentDivision()
     {
         return $this->belongsTo(Division::class, 'permanent_division_id');
     }
 
     public function permanentDistrict()
     {
         return $this->belongsTo(District::class, 'permanent_district_id');
     }
 
     public function permanentUpazila()
     {
         return $this->belongsTo(Upazila::class, 'permanent_upazila_id');
     }
 
     public function permanentUnion()
     {
         return $this->belongsTo(Union::class, 'permanent_union_id');
     }

     // local scope regularPatients
     public function scopeRegularPatients($query)
     {
         return $query->where('employment_status',  EmploymentEnum::Regular->value);
     }

     // medicines
     public function distribution_medicines()
     {
         return $this->hasMany(Distribution::class, 'patient_id');
     }

}
