<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'default_tariff',
        'standard_capacity',
        'is_active',
    ];

    protected $casts = [
        'default_tariff' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id');
    }

    public function guestTypeTariffs()
    {
        return $this->hasMany(GuestTypeTariff::class, 'room_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_type_id');
    }
}

