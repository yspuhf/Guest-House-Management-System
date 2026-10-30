<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'organization_unit_id',
        'employee_id',
        'name',
        'email',
        'password',
        'designation',
        'department',
        'phone',
        'primary_role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_has_roles', 'user_id', 'role_id');
    }

    public function managedGuestHouses()
    {
        return $this->hasMany(GuestHouse::class, 'assigned_incharge_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'applicant_id');
    }

    public function submittedBookings()
    {
        return $this->hasMany(Booking::class, 'submitted_by_id');
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->primary_role, $roles) || $this->roles()->whereIn('name', $roles)->exists();
        }
        return $this->primary_role === $roles || $this->roles()->where('name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->primary_role === 'system_admin') {
            return true;
        }

        return $this->roles()->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        })->exists();
    }
}

