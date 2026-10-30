<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\GuestHouse;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_bookings' => Booking::count(),
            'pending_verifications' => Booking::where('status', 'Verification Pending')->count(),
            'pending_approvals' => Booking::where('status', 'Approval Pending')->count(),
            'pending_allocations' => Booking::where('status', 'Approved')->count(),
            'active_checked_in' => Booking::where('status', 'Checked In')->count(),
            'my_bookings' => Booking::where('applicant_id', $user->id)->orWhere('submitted_by_id', $user->id)->count(),
            'total_guest_houses' => GuestHouse::where('is_active', true)->count(),
            'total_rooms' => Room::where('is_active', true)->count(),
            'revenue_total' => Payment::where('status', 'Successful')->sum('amount'),
        ];

        // Role-targeted queries
        $recentBookings = Booking::with(['applicant', 'guestHouse', 'roomType', 'guests', 'bookingRooms.room'])
            ->latest()
            ->take(8)
            ->get();

        $pendingApprovals = [];
        if ($user->hasRole(['approver', 'system_admin'])) {
            $pendingApprovals = Booking::where('status', 'Approval Pending')
                ->with(['applicant', 'guestHouse', 'roomType', 'guests'])
                ->latest()
                ->get();
        }

        $pendingVerifications = [];
        if ($user->hasRole(['verifier', 'system_admin'])) {
            $pendingVerifications = Booking::whereIn('status', ['Submitted', 'Verification Pending'])
                ->with(['applicant', 'guestHouse', 'roomType', 'guests'])
                ->latest()
                ->get();
        }

        $pendingAllocations = [];
        if ($user->hasRole(['guest_house_incharge', 'guest_house_admin', 'system_admin'])) {
            $pendingAllocations = Booking::where('status', 'Approved')
                ->with(['applicant', 'guestHouse', 'roomType', 'guests'])
                ->latest()
                ->get();
        }

        $activeStays = [];
        if ($user->hasRole(['guest_house_incharge', 'guest_house_admin', 'system_admin'])) {
            $activeStays = Booking::where('status', 'Checked In')
                ->with(['applicant', 'guestHouse', 'bookingRooms.room', 'guests'])
                ->latest()
                ->get();
        }

        return view('dashboard.index', compact(
            'stats',
            'recentBookings',
            'pendingApprovals',
            'pendingVerifications',
            'pendingAllocations',
            'activeStays'
        ));
    }
}

