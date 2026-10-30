<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoom extends Model
{
    protected $fillable = [
        'booking_id',
        'room_id',
        'allocated_by_id',
        'allocated_at',
        'checked_in_at',
        'checked_out_at',
        'status',
        'room_condition_on_checkin',
        'room_condition_on_checkout',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by_id');
    }
}

