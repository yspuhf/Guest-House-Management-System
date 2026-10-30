<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusHistory extends Model
{
    protected $table = 'booking_status_history';

    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'action',
        'actor_id',
        'remarks',
        'workflow_stage',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

