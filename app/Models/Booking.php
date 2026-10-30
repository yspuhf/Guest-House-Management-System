<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_reference',
        'applicant_id',
        'submitted_by_id',
        'guest_house_id',
        'room_type_id',
        'guest_type_id',
        'expenditure_head_id',
        'approval_workflow_id',
        'purpose',
        'arrival_date',
        'arrival_time',
        'departure_date',
        'departure_time',
        'billable_nights',
        'rooms_requested',
        'total_guests',
        'meal_preference',
        'special_requests',
        'accepted_terms',
        'status',
        'current_step_order',
        'applied_tariff_per_night',
        'total_room_charges',
        'total_tax_amount',
        'total_other_charges',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'payment_stage_applied',
        'payment_status',
        'cancellation_reason',
        'cancelled_by_id',
        'cancelled_at',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'applied_tariff_per_night' => 'decimal:2',
        'total_room_charges' => 'decimal:2',
        'total_tax_amount' => 'decimal:2',
        'total_other_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'accepted_terms' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function guestHouse()
    {
        return $this->belongsTo(GuestHouse::class, 'guest_house_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function guestType()
    {
        return $this->belongsTo(GuestType::class, 'guest_type_id');
    }

    public function expenditureHead()
    {
        return $this->belongsTo(ExpenditureHead::class, 'expenditure_head_id');
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function guests()
    {
        return $this->hasMany(BookingGuest::class, 'booking_id');
    }

    public function primaryGuest()
    {
        return $this->hasOne(BookingGuest::class, 'booking_id')->where('is_primary', true);
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class, 'booking_id');
    }

    public function allocatedRooms()
    {
        return $this->hasManyThrough(Room::class, BookingRoom::class, 'booking_id', 'id', 'id', 'room_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id')->orderBy('created_at', 'desc');
    }

    public function extensions()
    {
        return $this->hasMany(BookingExtension::class, 'booking_id')->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(BookingDocument::class, 'booking_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'booking_id');
    }

    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class, 'booking_id')->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

    // Status helpers
    public function isDraft(): bool { return $this->status === 'Draft'; }
    public function isSubmitted(): bool { return $this->status === 'Submitted'; }
    public function isVerificationPending(): bool { return $this->status === 'Verification Pending'; }
    public function isApprovalPending(): bool { return $this->status === 'Approval Pending'; }
    public function isApproved(): bool { return $this->status === 'Approved'; }
    public function isAllocated(): bool { return $this->status === 'Room Allocated'; }
    public function isCheckedIn(): bool { return $this->status === 'Checked In'; }
    public function isCheckedOut(): bool { return $this->status === 'Checked Out'; }
    public function isClosed(): bool { return $this->status === 'Closed'; }
    public function isCancelled(): bool { return $this->status === 'Cancelled'; }
    public function isRejected(): bool { return $this->status === 'Rejected'; }
    public function isReturned(): bool { return $this->status === 'Returned for Correction'; }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['Cancelled', 'Rejected', 'Checked In', 'Checked Out', 'Closed']);
    }
}

