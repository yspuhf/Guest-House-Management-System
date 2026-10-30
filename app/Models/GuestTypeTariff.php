<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestTypeTariff extends Model
{
    protected $fillable = [
        'guest_type_id',
        'room_type_id',
        'rate_per_night',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'rate_per_night' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function guestType()
    {
        return $this->belongsTo(GuestType::class, 'guest_type_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
}

