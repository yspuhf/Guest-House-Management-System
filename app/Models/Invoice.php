<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'booking_id',
        'invoice_date',
        'subtotal',
        'tax_total',
        'additional_charges',
        'discount_or_waiver',
        'grand_total',
        'paid_amount',
        'balance_due',
        'status',
        'is_finalized',
        'generated_by_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'discount_or_waiver' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'is_finalized' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function refunds()
    {
        return $this->hasMany(RefundOrAdjustment::class, 'invoice_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'Paid' || $this->balance_due <= 0;
    }
}

