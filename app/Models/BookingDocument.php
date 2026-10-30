<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDocument extends Model
{
    protected $fillable = [
        'booking_id',
        'document_title',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'uploaded_by_id',
        'is_confidential',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}

