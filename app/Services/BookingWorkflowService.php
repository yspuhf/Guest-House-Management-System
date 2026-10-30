<?php

namespace App\Services;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingWorkflowService
{
    protected NotificationService $notificationService;
    protected AuditService $auditService;

    public function __construct(NotificationService $notificationService, AuditService $auditService)
    {
        $this->notificationService = $notificationService;
        $this->auditService = $auditService;
    }

    /**
     * Submit a draft booking into the institutional workflow.
     */
    public function submitBooking(Booking $booking, User $actor, ?string $remarks = null): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $remarks) {
            $prevStatus = $booking->status;

            // Route to configured approval workflow or find matching one
            $workflow = $booking->workflow;
            if (!$workflow) {
                // Find default workflow for service type and org unit
                $workflow = ApprovalWorkflow::where('is_active', true)
                    ->where(function ($q) use ($booking) {
                        $q->where('organization_unit_id', $booking->applicant->organization_unit_id)
                          ->orWhereNull('organization_unit_id');
                    })
                    ->first();

                if ($workflow) {
                    $booking->approval_workflow_id = $workflow->id;
                }
            }

            // Determine first stage
            $firstStep = $workflow?->steps()->orderBy('step_order')->first();
            $newStatus = 'Verification Pending';
            $stepOrder = 1;

            if ($firstStep && $firstStep->stage_type === 'approver') {
                $newStatus = 'Approval Pending';
            } elseif (!$firstStep) {
                // If no hierarchy configured, route to authorized admin/incharge; never silently approve
                $newStatus = 'Verification Pending';
                $stepOrder = 0;
            }

            $booking->status = $newStatus;
            $booking->current_step_order = $stepOrder;
            $booking->save();

            // Record status history
            $this->recordHistory($booking, $prevStatus, $newStatus, 'Submitted', $actor->id, $remarks ?? 'Booking submitted for institutional verification', 'Submission');

            // Audit
            $this->auditService->log($actor, 'Submitted Booking', 'Workflow', Booking::class, $booking->id, ['status' => $prevStatus], ['status' => $newStatus]);

            // Notification
            $this->notificationService->notify($booking, 'booking_submitted', [
                'REMARKS' => $remarks ?? 'Submission successful',
            ]);

            return $booking;
        });
    }

    /**
     * Verify and forward the booking request.
     */
    public function verifyBooking(Booking $booking, User $actor, string $remarks): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $remarks) {
            $prevStatus = $booking->status;
            $workflow = $booking->workflow;

            // Find next step in workflow
            $nextStep = null;
            if ($workflow) {
                $nextStep = $workflow->steps()
                    ->where('step_order', '>', $booking->current_step_order)
                    ->orderBy('step_order')
                    ->first();
            }

            $newStatus = 'Approval Pending';
            $newOrder = $nextStep ? $nextStep->step_order : ($booking->current_step_order + 1);

            $booking->status = $newStatus;
            $booking->current_step_order = $newOrder;
            $booking->save();

            $this->recordHistory($booking, $prevStatus, $newStatus, 'Verified & Forwarded', $actor->id, $remarks, 'Verification');
            $this->auditService->log($actor, 'Verified Booking', 'Workflow', Booking::class, $booking->id, ['status' => $prevStatus], ['status' => $newStatus, 'remarks' => $remarks]);

            $this->notificationService->notify($booking, 'booking_verified', [
                'REMARKS' => $remarks,
            ]);

            return $booking;
        });
    }

    /**
     * Grant final approval to the booking request.
     */
    public function approveBooking(Booking $booking, User $actor, string $remarks): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $remarks) {
            $prevStatus = $booking->status;

            $newStatus = 'Approved';
            $booking->status = $newStatus;
            $booking->save();

            $this->recordHistory($booking, $prevStatus, $newStatus, 'Approved', $actor->id, $remarks, 'Approval');
            $this->auditService->log($actor, 'Approved Booking', 'Workflow', Booking::class, $booking->id, ['status' => $prevStatus], ['status' => $newStatus, 'remarks' => $remarks]);

            $this->notificationService->notify($booking, 'booking_approved', [
                'REMARKS' => $remarks,
            ]);

            return $booking;
        });
    }

    /**
     * Reject a booking with mandatory remarks.
     */
    public function rejectBooking(Booking $booking, User $actor, string $remarks): Booking
    {
        if (empty(trim($remarks))) {
            throw new \InvalidArgumentException('Mandatory remarks are required to reject a booking.');
        }

        return DB::transaction(function () use ($booking, $actor, $remarks) {
            $prevStatus = $booking->status;

            $booking->status = 'Rejected';
            $booking->save();

            // Release any allocated rooms if any
            $booking->bookingRooms()->update(['status' => 'Released']);

            $this->recordHistory($booking, $prevStatus, 'Rejected', 'Rejected', $actor->id, $remarks, 'Rejection');
            $this->auditService->log($actor, 'Rejected Booking', 'Workflow', Booking::class, $booking->id, ['status' => $prevStatus], ['status' => 'Rejected', 'remarks' => $remarks]);

            $this->notificationService->notify($booking, 'booking_rejected', [
                'REMARKS' => $remarks,
            ]);

            return $booking;
        });
    }

    /**
     * Return booking for correction if enabled by workflow policy.
     */
    public function returnForCorrection(Booking $booking, User $actor, string $remarks): Booking
    {
        if (empty(trim($remarks))) {
            throw new \InvalidArgumentException('Mandatory remarks are required to return a booking for correction.');
        }

        return DB::transaction(function () use ($booking, $actor, $remarks) {
            $prevStatus = $booking->status;

            $booking->status = 'Returned for Correction';
            $booking->save();

            $this->recordHistory($booking, $prevStatus, 'Returned for Correction', 'Returned for Correction', $actor->id, $remarks, 'Scrutiny');
            $this->auditService->log($actor, 'Returned Booking for Correction', 'Workflow', Booking::class, $booking->id, ['status' => $prevStatus], ['status' => 'Returned for Correction', 'remarks' => $remarks]);

            $this->notificationService->notify($booking, 'booking_returned', [
                'REMARKS' => $remarks,
            ]);

            return $booking;
        });
    }

    /**
     * Cancel a booking under institutional policy.
     */
    public function cancelBooking(Booking $booking, User $actor, string $reason): Booking
    {
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Cancellation reason is required.');
        }

        return DB::transaction(function () use ($booking, $actor, $reason) {
            $prevStatus = $booking->status;

            $booking->status = 'Cancelled';
            $booking->cancellation_reason = $reason;
            $booking->cancelled_by_id = $actor->id;
            $booking->cancelled_at = now();
            $booking->save();

            // Release allocated rooms immediately
            $booking->bookingRooms()->update(['status' => 'Released']);

            $this->recordHistory($booking, $prevStatus, 'Cancelled', 'Cancelled', $actor->id, $reason, 'Cancellation');
            $this->auditService->log($actor, 'Cancelled Booking', 'Bookings', Booking::class, $booking->id, ['status' => $prevStatus], ['status' => 'Cancelled', 'reason' => $reason]);

            return $booking;
        });
    }

    /**
     * Record immutable status history entry.
     */
    public function recordHistory(Booking $booking, ?string $fromStatus, string $toStatus, string $action, int $actorId, ?string $remarks, ?string $stage = null): BookingStatusHistory
    {
        return BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'action' => $action,
            'actor_id' => $actorId,
            'remarks' => $remarks,
            'workflow_stage' => $stage,
        ]);
    }
}

