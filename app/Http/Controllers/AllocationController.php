<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllocateRoomRequest;
use App\Models\Booking;
use App\Models\GuestHouse;
use App\Services\AuditService;
use App\Services\AvailabilityService;
use App\Services\BookingWorkflowService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllocationController extends Controller
{
    protected AvailabilityService $availabilityService;
    protected BookingWorkflowService $workflowService;
    protected AuditService $auditService;
    protected NotificationService $notificationService;

    public function __construct(
        AvailabilityService $availabilityService,
        BookingWorkflowService $workflowService,
        AuditService $auditService,
        NotificationService $notificationService
    ) {
        $this->availabilityService = $availabilityService;
        $this->workflowService = $workflowService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $approvedBookings = Booking::whereIn('status', ['Approved', 'Room Allocated'])
            ->with(['applicant', 'guestHouse', 'roomType', 'guests', 'bookingRooms.room'])
            ->latest()
            ->paginate(15);

        return view('allocation.index', compact('approvedBookings'));
    }

    public function showAllocationForm(Booking $booking)
    {
        // Re-check available rooms for the booking's exact stay interval
        $availableRooms = $this->availabilityService->findAvailableRooms(
            $booking->guest_house_id,
            $booking->room_type_id,
            $booking->arrival_date->toDateString(),
            $booking->departure_date->toDateString(),
            $booking->rooms_requested
        );

        $allocatedRoomIds = $booking->bookingRooms->pluck('room_id')->toArray();

        return view('allocation.allocate', compact('booking', 'availableRooms', 'allocatedRoomIds'));
    }

    public function storeAllocation(AllocateRoomRequest $request, Booking $booking)
    {
        $user = Auth::user();

        try {
            $allocatedNumbers = $this->availabilityService->allocateRoomsSafely(
                $booking,
                $request->room_ids,
                $user->id
            );

            $prevStatus = $booking->status;
            $booking->status = 'Room Allocated';
            $booking->save();

            $remarks = 'Rooms assigned: ' . implode(', ', $allocatedNumbers) . ($request->notes ? ' - ' . $request->notes : '');

            $this->workflowService->recordHistory(
                $booking,
                $prevStatus,
                'Room Allocated',
                'Room Allocated',
                $user->id,
                $remarks,
                'Allocation'
            );

            $this->auditService->log($user, 'Allocated Rooms', 'Allocation', Booking::class, $booking->id, ['status' => $prevStatus], [
                'status' => 'Room Allocated',
                'rooms' => $allocatedNumbers,
            ]);

            $this->notificationService->notify($booking, 'room_allocated', [
                'ROOM_NUMBERS' => implode(', ', $allocatedNumbers),
            ]);

            return redirect()->route('allocations.index')->with('success', "Rooms [ " . implode(', ', $allocatedNumbers) . " ] allocated to booking {$booking->booking_reference}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Allocation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function calendar(Request $request)
    {
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $selectedGhId = $request->input('guest_house_id', $guestHouses->first()?->id ?? 1);
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::today();

        $calendarData = $this->availabilityService->getCalendarMatrix($selectedGhId, $startDate, 14);

        return view('allocation.calendar', compact('guestHouses', 'selectedGhId', 'startDate', 'calendarData'));
    }
}

