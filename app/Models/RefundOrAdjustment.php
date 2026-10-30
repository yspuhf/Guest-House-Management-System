<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundOrAdjustment extends Model
{
    protected $table = 'refunds_or_adjustments';

    protected $fillable = [
        'invoice_id',
        'payment_id',
        'type',
        'amount',
        'reason',
        'authorized_by_id',
        'reference_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by_id');
    }
}

