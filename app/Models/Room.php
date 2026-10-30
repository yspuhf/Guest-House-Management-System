<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'guest_house_id',
        'room_type_id',
        'room_number',
        'floor',
        'tariff',
        'max_occupancy',
        'facilities',
        'is_active',
        'publication_status',
        'maintenance_status',
        'notes',
    ];

    protected $casts = [
        'tariff' => 'decimal:2',
        'facilities' => 'array',
        'is_active' => 'boolean',
    ];

    public function guestHouse()
    {
        return $this->belongsTo(GuestHouse::class, 'guest_house_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class, 'room_id');
    }

    public function blocks()
    {
        return $this->hasMany(RoomBlock::class, 'room_id');
    }

    public function isAvailableForStay(string $arrivalDate, string $departureDate): bool
    {
        if (!$this->is_active || $this->publication_status !== 'published' || $this->maintenance_status !== 'operational') {
            return false;
        }

        // Check maintenance/hold blocks: [start_date, end_date)
        $hasBlock = $this->blocks()
            ->where('is_active', true)
            ->where(function ($query) use ($arrivalDate, $departureDate) {
                $query->where('start_date', '<', $departureDate)
                      ->where('end_date', '>', $arrivalDate);
            })->exists();

        if ($hasBlock) {
            return false;
        }

        // Check confirmed/active bookings: [arrival_date, departure_date)
        $hasOverlap = $this->bookingRooms()
            ->whereHas('booking', function ($query) use ($arrivalDate, $departureDate) {
                $query->whereNotIn('status', ['Cancelled', 'Rejected', 'Closed', 'Draft', 'Returned for Correction'])
                      ->where('arrival_date', '<', $departureDate)
                      ->where('departure_date', '>', $arrivalDate);
            })
            ->whereIn('status', ['Allocated', 'Occupied'])
            ->exists();

        return !$hasOverlap;
    }
}

