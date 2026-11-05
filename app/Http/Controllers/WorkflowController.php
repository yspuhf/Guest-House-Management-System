<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkflowActionRequest;
use App\Models\Booking;
use App\Services\AvailabilityService;
use App\Services\BookingWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    protected BookingWorkflowService $workflowService;
    protected AvailabilityService $availabilityService;

    public function __construct(
        BookingWorkflowService $workflowService,
        AvailabilityService $availabilityService
    ) {
        $this->workflowService = $workflowService;
        $this->availabilityService = $availabilityService;
    }

    public function pendingQueue(Request $request)
    {
        $user = Auth::user();
        $isVerifier = $user->hasRole(['verifier', 'system_admin']);
        $isApprover = $user->hasRole(['approver', 'system_admin']);

        $verifications = [];
        if ($isVerifier) {
            $verifications = Booking::whereIn('status', ['Submitted', 'Verification Pending'])
                ->with(['applicant', 'guestHouse', 'roomType', 'guests', 'expenditureHead', 'documents'])
                ->latest()
                ->get();
        }

        $approvals = [];
        if ($isApprover) {
            $approvals = Booking::where('status', 'Approval Pending')
                ->with(['applicant', 'guestHouse', 'roomType', 'guests', 'expenditureHead', 'documents'])
                ->latest()
                ->get();
        }

        return view('workflow.pending', compact('verifications', 'approvals', 'isVerifier', 'isApprover'));
    }

    public function handleAction(WorkflowActionRequest $request, Booking $booking)
    {
        $user = Auth::user();
        $action = $request->action;
        $remarks = $request->remarks ?? '';

        switch ($action) {
            case 'verify':
                $this->workflowService->verifyBooking($booking, $user, $remarks ?: 'Booking details and documents verified');
                return back()->with('success', "Booking {$booking->booking_reference} has been verified and forwarded for approval.");

            case 'approve':
                // Check if room allotment is provided during approval
                if ($request->filled('room_id') || $request->filled('room_ids')) {
                    $roomIds = $request->filled('room_ids') ? (array)$request->room_ids : [(int)$request->room_id];
                    $checkIn = $booking->arrival_date->toDateString();
                    $checkOut = $booking->departure_date->toDateString();

                    // Strict date-wise availability validation to prevent double booking or overlapping allocations
                    foreach ($roomIds as $rId) {
                        if (!$this->availabilityService->isRoomAvailable($rId, $checkIn, $checkOut, $booking->id)) {
                            return back()->with('error', "DOUBLE BOOKING CONFLICT DETECTED: Room ID {$rId} is already occupied or reserved between {$checkIn} and {$checkOut}.");
                        }
                    }

                    $allocatedNumbers = $this->availabilityService->allocateRoomsSafely($booking, $roomIds, $user->id);
                    $booking->allotted_room_id = $roomIds[0];
                    $booking->room_allotted_at = now();
                    $booking->allotted_by_id = $user->id;
                    $booking->save();

                    $remarks = ($remarks ? $remarks . ' - ' : '') . 'Room Allotted: ' . implode(', ', $allocatedNumbers);
                }

                $this->workflowService->approveBooking($booking, $user, $remarks ?: 'Booking approved by authority');
                return back()->with('success', "Booking {$booking->booking_reference} has been approved" . ($request->filled('room_id') ? " and room allotted successfully." : "."));

            case 'reject':
                $this->workflowService->rejectBooking($booking, $user, $remarks);
                return back()->with('warning', "Booking {$booking->booking_reference} has been rejected.");

            case 'return':
                $this->workflowService->returnForCorrection($booking, $user, $remarks);
                return back()->with('info', "Booking {$booking->booking_reference} has been returned for correction.");

            default:
                return back()->with('error', 'Invalid workflow action requested.');
        }
    }
}
