<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->hasRole(['system_admin', 'guest_house_admin', 'guest_house_incharge', 'accounts_finance', 'report_auditor'])) {
            return true;
        }

        if ($user->hasRole('verifier') && in_array($booking->status, ['Submitted', 'Verification Pending'])) {
            return true;
        }

        if ($user->hasRole('approver') && in_array($booking->status, ['Verification Pending', 'Approval Pending'])) {
            return true;
        }

        return $booking->applicant_id === $user->id || $booking->submitted_by_id === $user->id;
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($booking->isDraft() || $booking->isReturned()) {
            return $booking->applicant_id === $user->id || $booking->submitted_by_id === $user->id;
        }

        return false;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if (!$booking->canBeCancelled()) {
            return false;
        }

        if ($user->hasRole(['system_admin', 'guest_house_admin', 'guest_house_incharge'])) {
            return true;
        }

        return $booking->applicant_id === $user->id || $booking->submitted_by_id === $user->id;
    }

    public function verify(User $user, Booking $booking): bool
    {
        return $user->hasRole(['verifier', 'system_admin']) && in_array($booking->status, ['Submitted', 'Verification Pending']);
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $user->hasRole(['approver', 'system_admin']) && in_array($booking->status, ['Approval Pending', 'Verification Pending']);
    }

    public function allocate(User $user, Booking $booking): bool
    {
        return $user->hasRole(['guest_house_incharge', 'guest_house_admin', 'system_admin']) && in_array($booking->status, ['Approved', 'Room Allocated']);
    }

    public function checkIn(User $user, Booking $booking): bool
    {
        return $user->hasRole(['guest_house_incharge', 'system_admin']) && $booking->status === 'Room Allocated';
    }

    public function checkOut(User $user, Booking $booking): bool
    {
        return $user->hasRole(['guest_house_incharge', 'system_admin']) && $booking->status === 'Checked In';
    }
}

