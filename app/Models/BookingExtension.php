<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingExtension extends Model
{
    protected $fillable = [
        'booking_id',
        'original_departure_date',
        'requested_departure_date',
        'additional_nights',
        'additional_charges',
        'reason',
        'status',
        'requested_by_id',
        'decided_by_id',
        'decision_remarks',
        'decided_at',
    ];

    protected $casts = [
        'original_departure_date' => 'date',
        'requested_departure_date' => 'date',
        'additional_charges' => 'decimal:2',
        'decided_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by_id');
    }
}

