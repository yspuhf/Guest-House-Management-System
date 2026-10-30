<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingGuest;
use App\Models\ExpenditureHead;
use App\Models\GuestHouse;
use App\Models\GuestType;
use App\Models\RoomType;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\BookingWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected BookingWorkflowService $workflowService;
    protected BillingService $billingService;
    protected AuditService $auditService;

    public function __construct(
        BookingWorkflowService $workflowService,
        BillingService $billingService,
        AuditService $auditService
    ) {
        $this->workflowService = $workflowService;
        $this->billingService = $billingService;
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Booking::with(['guestHouse', 'roomType', 'guestType', 'applicant', 'guests', 'bookingRooms.room']);

        // Scope to user unless privileged
        if (!$user->hasRole(['system_admin', 'guest_house_admin', 'guest_house_incharge', 'accounts_finance', 'report_auditor', 'verifier', 'approver'])) {
            $query->where(function ($q) use ($user) {
                $q->where('applicant_id', $user->id)
                  ->orWhere('submitted_by_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', $request->guest_house_id);
        }

        if ($request->filled('reference')) {
            $query->where('booking_reference', 'like', "%{$request->reference}%");
        }

        $bookings = $query->latest()->paginate(15);
        $guestHouses = GuestHouse::where('is_active', true)->get();

        return view('bookings.index', compact('bookings', 'guestHouses'));
    }

    public function create()
    {
        $guestHouses = GuestHouse::where('status', 'published')->where('is_active', true)->get();
        $roomTypes = RoomType::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->orderBy('priority_order')->get();
        $expenditureHeads = ExpenditureHead::where('is_active', true)->get();

        return view('bookings.create', compact('guestHouses', 'roomTypes', 'guestTypes', 'expenditureHeads'));
    }

    public function store(StoreBookingRequest $request)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($request, $user) {
            $year = date('Y');
            $count = Booking::whereYear('created_at', $year)->count() + 1;
            $ref = sprintf('UGH-%s-%04d', $year, $count);

            $nights = max(1, Carbon::parse($request->arrival_date)->diffInDays(Carbon::parse($request->departure_date)));

            $booking = Booking::create([
                'booking_reference' => $ref,
                'applicant_id' => $user->id,
                'submitted_by_id' => $user->id,
                'guest_house_id' => $request->guest_house_id,
                'room_type_id' => $request->room_type_id,
                'guest_type_id' => $request->guest_type_id,
                'expenditure_head_id' => $request->expenditure_head_id,
                'purpose' => $request->purpose,
                'arrival_date' => $request->arrival_date,
                'arrival_time' => $request->input('arrival_time', '12:00:00'),
                'departure_date' => $request->departure_date,
                'departure_time' => $request->input('departure_time', '11:00:00'),
                'billable_nights' => $nights,
                'rooms_requested' => $request->rooms_requested,
                'total_guests' => $request->total_guests,
                'meal_preference' => $request->meal_preference,
                'special_requests' => $request->special_requests,
                'accepted_terms' => true,
                'status' => 'Draft',
            ]);

            // Create Primary Guest
            BookingGuest::create([
                'booking_id' => $booking->id,
                'name' => $request->primary_guest_name,
                'designation' => $request->primary_guest_designation,
                'phone' => $request->primary_guest_phone,
                'email' => $request->primary_guest_email,
                'id_document_type' => $request->primary_guest_id_type,
                'id_document_number' => $request->primary_guest_id_number,
                'is_primary' => true,
                'relationship' => 'Self / Primary Guest',
            ]);

            // Accompanying guests
            if ($request->has('accompanying_guests')) {
                foreach ($request->accompanying_guests as $g) {
                    if (!empty($g['name'])) {
                        BookingGuest::create([
                            'booking_id' => $booking->id,
                            'name' => $g['name'],
                            'relationship' => $g['relationship'] ?? 'Accompanying',
                            'is_primary' => false,
                        ]);
                    }
                }
            }

            // Upload Document if provided
            if ($request->hasFile('approval_document')) {
                $file = $request->file('approval_document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('private/documents', $filename);

                BookingDocument::create([
                    'booking_id' => $booking->id,
                    'document_title' => 'Official Approval / Sanction Letter',
                    'document_type' => 'approval_letter',
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size_bytes' => $file->getSize(),
                    'uploaded_by_id' => $user->id,
                ]);
            }

            // Estimate charges
            $charges = $this->billingService->computeBookingCharges($booking);
            $booking->update([
                'applied_tariff_per_night' => $charges['rate_per_night'],
                'total_room_charges' => $charges['room_charges'],
                'total_tax_amount' => $charges['tax_amount'],
                'grand_total' => $charges['grand_total'],
                'balance_amount' => $charges['grand_total'],
            ]);

            $this->auditService->log($user, 'Saved Draft Booking', 'Bookings', Booking::class, $booking->id, null, $booking->toArray());

            // If user clicked 'Submit Request' instead of 'Save Draft'
            if ($request->input('action_type') === 'submit') {
                $this->workflowService->submitBooking($booking, $user, 'Initial request submission by applicant');
                return redirect()->route('bookings.show', $booking)->with('success', "Booking {$booking->booking_reference} submitted successfully and routed for verification.");
            }

            return redirect()->route('bookings.show', $booking)->with('success', "Booking draft {$booking->booking_reference} saved successfully.");
        });
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'applicant',
            'submittedBy',
            'guestHouse.incharge',
            'roomType',
            'guestType',
            'expenditureHead',
            'guests',
            'bookingRooms.room',
            'statusHistory.actor',
            'documents',
            'invoices.items',
            'invoices.payments',
            'extensions',
        ]);

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        // Only Draft or Returned for Correction can be edited
        if (!$booking->isDraft() && !$booking->isReturned()) {
            return redirect()->route('bookings.show', $booking)->with('error', 'Only Draft or Returned for Correction bookings can be edited.');
        }

        $guestHouses = GuestHouse::where('status', 'published')->where('is_active', true)->get();
        $roomTypes = RoomType::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->orderBy('priority_order')->get();
        $expenditureHeads = ExpenditureHead::where('is_active', true)->get();

        return view('bookings.edit', compact('booking', 'guestHouses', 'roomTypes', 'guestTypes', 'expenditureHeads'));
    }

    public function update(StoreBookingRequest $request, Booking $booking)
    {
        if (!$booking->isDraft() && !$booking->isReturned()) {
            return redirect()->route('bookings.show', $booking)->with('error', 'This booking cannot be edited in its current status.');
        }

        $user = Auth::user();

        return DB::transaction(function () use ($request, $booking, $user) {
            $nights = max(1, Carbon::parse($request->arrival_date)->diffInDays(Carbon::parse($request->departure_date)));

            $booking->update([
                'guest_house_id' => $request->guest_house_id,
                'room_type_id' => $request->room_type_id,
                'guest_type_id' => $request->guest_type_id,
                'expenditure_head_id' => $request->expenditure_head_id,
                'purpose' => $request->purpose,
                'arrival_date' => $request->arrival_date,
                'arrival_time' => $request->input('arrival_time', '12:00:00'),
                'departure_date' => $request->departure_date,
                'departure_time' => $request->input('departure_time', '11:00:00'),
                'billable_nights' => $nights,
                'rooms_requested' => $request->rooms_requested,
                'total_guests' => $request->total_guests,
                'meal_preference' => $request->meal_preference,
                'special_requests' => $request->special_requests,
            ]);

            // Update primary guest
            $primaryGuest = $booking->primaryGuest;
            if ($primaryGuest) {
                $primaryGuest->update([
                    'name' => $request->primary_guest_name,
                    'designation' => $request->primary_guest_designation,
                    'phone' => $request->primary_guest_phone,
                    'email' => $request->primary_guest_email,
                    'id_document_type' => $request->primary_guest_id_type,
                    'id_document_number' => $request->primary_guest_id_number,
                ]);
            }

            // Recalculate charges
            $charges = $this->billingService->computeBookingCharges($booking);
            $booking->update([
                'applied_tariff_per_night' => $charges['rate_per_night'],
                'total_room_charges' => $charges['room_charges'],
                'total_tax_amount' => $charges['tax_amount'],
                'grand_total' => $charges['grand_total'],
                'balance_amount' => round($charges['grand_total'] - $booking->paid_amount, 2),
            ]);

            if ($request->input('action_type') === 'submit') {
                $this->workflowService->submitBooking($booking, $user, 'Resubmitted after correction');
                return redirect()->route('bookings.show', $booking)->with('success', "Booking {$booking->booking_reference} resubmitted for verification.");
            }

            return redirect()->route('bookings.show', $booking)->with('success', "Booking {$booking->booking_reference} updated.");
        });
    }

    public function submitDraft(Booking $booking)
    {
        $this->workflowService->submitBooking($booking, Auth::user(), 'Submitted from draft');
        return back()->with('success', "Booking {$booking->booking_reference} submitted successfully.");
    }

    public function cancel(Request $request, Booking $booking)
    {
        $request->validate(['cancellation_reason' => 'required|string|min:5|max:1000']);
        $this->workflowService->cancelBooking($booking, Auth::user(), $request->cancellation_reason);
        return back()->with('success', "Booking {$booking->booking_reference} has been cancelled.");
    }

    public function track(Request $request)
    {
        $booking = null;
        if ($request->filled('reference')) {
            $booking = Booking::where('booking_reference', trim($request->reference))
                ->with(['guestHouse', 'roomType', 'statusHistory.actor', 'guests', 'bookingRooms.room'])
                ->first();
        }
        return view('bookings.track', compact('booking'));
    }

    public function printVoucher(Booking $booking)
    {
        $booking->load(['applicant', 'guestHouse', 'roomType', 'guestType', 'guests', 'bookingRooms.room', 'statusHistory']);
        return view('bookings.print', compact('booking'));
    }
}

