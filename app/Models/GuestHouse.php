<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestHouse extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'address',
        'eligibility_rules',
        'assigned_incharge_id',
        'contact_person',
        'contact_phone',
        'contact_email',
        'status',
        'is_active',
        'supporting_document_path',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function incharge()
    {
        return $this->belongsTo(User::class, 'assigned_incharge_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'guest_house_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'guest_house_id');
    }

    public function roomBlocks()
    {
        return $this->hasMany(RoomBlock::class, 'guest_house_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}

