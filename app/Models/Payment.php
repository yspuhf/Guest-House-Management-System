<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'receipt_number',
        'invoice_id',
        'booking_id',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'amount',
        'status',
        'notes',
        'collected_by_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by_id');
    }

    public function refunds()
    {
        return $this->hasMany(RefundOrAdjustment::class, 'payment_id');
    }
}

