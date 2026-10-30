<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_description',
        'item_type',
        'unit_rate',
        'quantity',
        'line_total',
        'tax_id',
        'tax_rate_applied',
    ];

    protected $casts = [
        'unit_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_rate_applied' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}

