<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenditureHead extends Model
{
    protected $fillable = [
        'code',
        'name',
        'department',
        'description',
        'requires_approval_document',
        'is_active',
    ];

    protected $casts = [
        'requires_approval_document' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'expenditure_head_id');
    }
}

