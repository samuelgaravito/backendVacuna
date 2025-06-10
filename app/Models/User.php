<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Make sure to import HasMany

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cedula',
        'name',
        'email',
        'password',
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

    // --- Relationships ---

    /**
     * Define the many-to-many relationship with roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Define the one-to-many relationship with Representados.
     * A user can have many Representados.
     */
    public function representados(): HasMany
    {
        return $this->hasMany(Representado::class, 'user_id');
    }

    // --- Role Management Methods (as provided by you) ---

    /**
     * Checks if the user has a specific role (by name or by Role instance).
     */
    public function hasRole(string|Role $role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return $role instanceof Role
            ? $this->roles->contains('id', $role->id)
            : false;
    }

    /**
     * Checks if the user has ANY of the given roles.
     *
     * @param array<string|Role>|string $roles Role names or a Role instance (or an array of them).
     * @return bool
     */
    public function hasAnyRole(array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}