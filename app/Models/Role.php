<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relationship role with permission
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    // Relationship role with user
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // local scope role index
    public function scopeIndex($query)
    {
        return $query->orderBy('index', 'DESC');
    }
}
