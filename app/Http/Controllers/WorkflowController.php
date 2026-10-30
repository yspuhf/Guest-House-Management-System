<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkflowActionRequest;
use App\Models\Booking;
use App\Services\BookingWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    protected BookingWorkflowService $workflowService;

    public function __construct(BookingWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
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
                $this->workflowService->approveBooking($booking, $user, $remarks ?: 'Booking approved by authority');
                return back()->with('success', "Booking {$booking->booking_reference} has been approved.");

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

