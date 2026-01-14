<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use HasFactory;

    protected $table = 'fiscal_years';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    /**
     * Scope: active fiscal year
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isUsed(): bool
    {
        // future-proof (later auto/driver registration check add হবে)
        return false;
    }

    public static function getActiveFiscalYear(): ?self
    {
        return self::where('is_active', true)->first();
    }
}
