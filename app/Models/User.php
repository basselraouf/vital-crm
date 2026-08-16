<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username', 'email', 'password',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function validatePassportPassword($value): bool
    {
        return Hash::check($value, $this->password);
    }

    // ── Super Admin Protection ─────────────────────────────────────────────

    /** Is this the immutable super_admin account? */
    public function isSuperAdmin(): bool
    {
        return $this->email === 'superadmin@vital-crm.com';
    }

    /**
     * Prevent deletion of the super_admin user.
     * Called automatically by Eloquent before any delete.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->isSuperAdmin()) {
                throw new \RuntimeException('The Super Admin account cannot be deleted.');
            }
        });
    }

}

