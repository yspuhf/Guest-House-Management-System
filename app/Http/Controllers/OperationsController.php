<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Http\Requests\ExtensionRequest;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\GuestHouse;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Services\AuditService;
use App\Services\AvailabilityService;
use App\Services\BillingService;
use App\Services\BookingWorkflowService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    protected AvailabilityService $availabilityService;
    protected BookingWorkflowService $workflowService;
    protected BillingService $billingService;
    protected AuditService $auditService;
    protected NotificationService $notificationService;

    public function __construct(
        AvailabilityService $availabilityService,
        BookingWorkflowService $workflowService,
        BillingService $billingService,
        AuditService $auditService,
        NotificationService $notificationService
    ) {
        $this->availabilityService = $availabilityService;
        $this->workflowService = $workflowService;
        $this->billingService = $billingService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function arrivals()
    {
        $todayArrivals = Booking::whereIn('status', ['Room Allocated', 'Approved'])
            ->with(['applicant', 'guestHouse', 'roomType', 'guests', 'bookingRooms.room'])
            ->latest()
            ->paginate(15);

        return view('operations.arrivals', compact('todayArrivals'));
    }

    public function activeStays()
    {
        $activeStays = Booking::where('status', 'Checked In')
            ->with(['applicant', 'guestHouse', 'roomType', 'guests', 'bookingRooms.room', 'invoices'])
            ->latest()
            ->paginate(15);

        return view('operations.active_stays', compact('activeStays'));
    }

    public function processCheckIn(CheckInRequest $request, Booking $booking)
    {
        $user = Auth::user();

        // Enforce payment stage if configured as 'check_in'
        if (!$this->billingService->validatePaymentStage($booking, 'check_in')) {
            return back()->with('error', 'Policy requires payment settlement prior to check-in.');
        }

        return DB::transaction(function () use ($request, $booking, $user) {
            $prevStatus = $booking->status;

            $booking->status = 'Checked In';
            $booking->save();

            // Update assigned rooms to Occupied
            $booking->bookingRooms()->update([
                'status' => 'Occupied',
                'checked_in_at' => now(),
                'room_condition_on_checkin' => $request->room_condition ?? 'Good condition upon guest arrival',
            ]);

            $this->workflowService->recordHistory(
                $booking,
                $prevStatus,
                'Checked In',
                'Checked In',
                $user->id,
                'Guest arrived and photo ID verified. Condition: ' . ($request->room_condition ?? 'Normal'),
                'Stay Operations'
            );

            $this->auditService->log($user, 'Checked In Guest', 'Operations', Booking::class, $booking->id);

            $this->notificationService->notify($booking, 'guest_checked_in');

            return back()->with('success', "Guest checked in for booking {$booking->booking_reference}.");
        });
    }

    public function processCheckOut(CheckOutRequest $request, Booking $booking)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($request, $booking, $user) {
            $prevStatus = $booking->status;

            // Generate or finalize invoice with any incidentals
            $additionalItems = $request->input('additional_charges', []);
            $invoice = $this->billingService->generateInvoice($booking, $user, $additionalItems);

            // Update assigned rooms to Vacated
            $booking->bookingRooms()->update([
                'status' => 'Vacated',
                'checked_out_at' => now(),
                'room_condition_on_checkout' => $request->room_condition,
            ]);

            // If balance is zero, mark as Closed; else Checked Out pending payment
            $booking->status = ($invoice->balance_due <= 0) ? 'Closed' : 'Checked Out';
            $booking->save();

            $this->workflowService->recordHistory(
                $booking,
                $prevStatus,
                $booking->status,
                'Checked Out',
                $user->id,
                "Check-out departure recorded. Room condition: {$request->room_condition}",
                'Stay Operations'
            );

            $this->auditService->log($user, 'Checked Out Guest', 'Operations', Booking::class, $booking->id, null, [
                'condition' => $request->room_condition,
                'invoice' => $invoice->invoice_number,
                'balance' => $invoice->balance_due,
            ]);

            return redirect()->route('billing.invoice', $invoice)->with('success', "Check-out completed for booking {$booking->booking_reference}. Invoice #{$invoice->invoice_number} generated.");
        });
    }

    public function requestExtension(ExtensionRequest $request, Booking $booking)
    {
        $user = Auth::user();
        $newDep = Carbon::parse($request->requested_departure_date);
        $currDep = Carbon::parse($booking->departure_date);

        if ($newDep->lte($currDep)) {
            return back()->withErrors(['requested_departure_date' => 'Requested extension date must be strictly after the current departure date.']);
        }

        $extraNights = $currDep->diffInDays($newDep);
        $extraCharges = round($extraNights * (float)$booking->applied_tariff_per_night * $booking->rooms_requested, 2);

        $ext = BookingExtension::create([
            'booking_id' => $booking->id,
            'original_departure_date' => $currDep->toDateString(),
            'requested_departure_date' => $newDep->toDateString(),
            'additional_nights' => $extraNights,
            'additional_charges' => $extraCharges,
            'reason' => $request->reason,
            'status' => 'Pending',
            'requested_by_id' => $user->id,
        ]);

        $this->auditService->log($user, 'Requested Stay Extension', 'Operations', BookingExtension::class, $ext->id);

        return back()->with('success', "Extension request for {$extraNights} additional night(s) submitted for authority review.");
    }

    public function decideExtension(Request $request, BookingExtension $extension)
    {
        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'remarks' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $booking = $extension->booking;

        if ($request->decision === 'Approved') {
            // Re-verify availability of currently allocated rooms for extended interval
            foreach ($booking->bookingRooms as $br) {
                $isAvailable = $this->availabilityService->isRoomAvailable(
                    $br->room_id,
                    $extension->original_departure_date->toDateString(),
                    $extension->requested_departure_date->toDateString(),
                    $booking->id
                );

                if (!$isAvailable) {
                    return back()->with('error', "Cannot approve extension: Room {$br->room->room_number} is already reserved for a subsequent booking.");
                }
            }

            $booking->departure_date = $extension->requested_departure_date;
            $booking->billable_nights += $extension->additional_nights;
            $booking->save();

            // Recompute billing
            $charges = $this->billingService->computeBookingCharges($booking);
            $booking->update([
                'total_room_charges' => $charges['room_charges'],
                'total_tax_amount' => $charges['tax_amount'],
                'grand_total' => $charges['grand_total'],
                'balance_amount' => round($charges['grand_total'] - $booking->paid_amount, 2),
            ]);
        }

        $extension->update([
            'status' => $request->decision,
            'decided_by_id' => $user->id,
            'decision_remarks' => $request->remarks,
            'decided_at' => now(),
        ]);

        $this->auditService->log($user, "{$request->decision} Stay Extension", 'Operations', BookingExtension::class, $extension->id);

        return back()->with('success', "Stay extension request was {$request->decision}.");
    }

    public function manageBlocks(Request $request)
    {
        $query = RoomBlock::with(['room.guestHouse', 'blockedBy']);

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', $request->guest_house_id);
        }

        $blocks = $query->latest()->paginate(15);
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $rooms = Room::where('is_active', true)->get();

        return view('operations.room_blocks', compact('blocks', 'guestHouses', 'rooms'));
    }

    public function storeBlock(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'block_type' => 'required|in:maintenance,deep_cleaning,vip_hold,administrative_block',
            'reason' => 'required|string|max:500',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        $block = RoomBlock::create([
            'room_id' => $room->id,
            'guest_house_id' => $room->guest_house_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'block_type' => $validated['block_type'],
            'reason' => $validated['reason'],
            'blocked_by_id' => Auth::id(),
            'is_active' => true,
        ]);

        $this->auditService->log(Auth::user(), 'Created Room Block', 'Operations', RoomBlock::class, $block->id);

        return back()->with('success', "Room block created for Room {$room->room_number}.");
    }

    public function removeBlock(RoomBlock $block)
    {
        $block->update(['is_active' => false]);
        $this->auditService->log(Auth::user(), 'Deactivated Room Block', 'Operations', RoomBlock::class, $block->id);

        return back()->with('success', 'Room block removed.');
    }
}

