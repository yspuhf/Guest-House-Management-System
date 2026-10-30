<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestType extends Model
{
    protected $fillable = ['code', 'name', 'description', 'priority_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tariffs()
    {
        return $this->hasMany(GuestTypeTariff::class, 'guest_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'guest_type_id');
    }
}

