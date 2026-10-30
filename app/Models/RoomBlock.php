<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomBlock extends Model
{
    protected $fillable = [
        'room_id',
        'guest_house_id',
        'start_date',
        'end_date',
        'block_type',
        'reason',
        'blocked_by_id',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function guestHouse()
    {
        return $this->belongsTo(GuestHouse::class, 'guest_house_id');
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by_id');
    }
}

