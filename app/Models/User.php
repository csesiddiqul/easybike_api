<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_name',
        'role_id',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Generate password reset token
    public function createPasswordResetToken()
    {
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->email,
            'token' => $token,
            'created_at' => now(),
        ]);
        return $token;
    }

    // Validate reset token
    public function isValidPasswordResetToken($token)
    {
        return $this->password_reset_token === $token
            && $this->password_reset_token_created_at->gt(now()->subHours(2)); // Token expires after 2 hours
    }

    // Relationship user with role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Check role exists
    public function hasRole($role)
    {
        return $this->role->name === $role;
    }

    // Check permission exists
    public function hasPermission($permission)
    {
        return $this->role->permissions->contains('slug', $permission);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
}
