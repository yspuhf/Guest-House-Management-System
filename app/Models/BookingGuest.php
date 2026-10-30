<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    protected $fillable = [
        'booking_id',
        'name',
        'designation',
        'phone',
        'email',
        'id_document_type',
        'id_document_number',
        'is_primary',
        'relationship',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}

