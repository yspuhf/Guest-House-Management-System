<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuestHouse;
use App\Models\GuestType;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function bookingsReport(Request $request)
    {
        $query = Booking::with(['applicant', 'guestHouse', 'roomType', 'guestType', 'bookingRooms.room', 'expenditureHead', 'primaryGuest']);

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', $request->guest_house_id);
        }

        if ($request->filled('guest_type_id')) {
            $query->where('guest_type_id', $request->guest_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('arrival_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportBookingsCsv($query->get());
        }

        $bookings = $query->latest()->paginate(25)->withQueryString();
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->get();

        return view('reports.bookings', compact('bookings', 'guestHouses', 'guestTypes'));
    }

    public function availabilityReport(Request $request)
    {
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $selectedGhId = $request->input('guest_house_id', $guestHouses->first()?->id ?? 1);
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today()->addDays(7);

        $rooms = Room::where('guest_house_id', $selectedGhId)
            ->with(['roomType', 'blocks' => function ($q) use ($startDate, $endDate) {
                $q->where('is_active', true)
                  ->where('start_date', '<', $endDate->toDateString())
                  ->where('end_date', '>', $startDate->toDateString());
            }, 'bookingRooms' => function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', ['Allocated', 'Occupied'])
                  ->whereHas('booking', function ($b) use ($startDate, $endDate) {
                      $b->whereNotIn('status', ['Cancelled', 'Rejected', 'Closed', 'Draft', 'Returned for Correction'])
                        ->where('arrival_date', '<', $endDate->toDateString())
                        ->where('departure_date', '>', $startDate->toDateString());
                  })->with('booking.primaryGuest');
            }])
            ->get();

        return view('reports.availability', compact('guestHouses', 'selectedGhId', 'startDate', 'endDate', 'rooms'));
    }

    public function financialReport(Request $request)
    {
        $query = Invoice::with(['booking.guestHouse', 'booking.applicant', 'payments', 'generatedBy']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('invoice_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalBilled = (clone $query)->sum('grand_total');
        $totalCollected = (clone $query)->sum('paid_amount');
        $totalOutstanding = (clone $query)->sum('balance_due');

        $invoices = $query->latest()->paginate(25)->withQueryString();

        return view('reports.financial', compact('invoices', 'totalBilled', 'totalCollected', 'totalOutstanding'));
    }

    protected function exportBookingsCsv($bookings): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="university_guest_house_bookings_' . date('Ymd_His') . '.csv"',
        ];

        return new StreamedResponse(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Booking Reference',
                'Applicant Name',
                'Applicant Department',
                'Primary Guest',
                'Guest House',
                'Room Type',
                'Arrival Date',
                'Departure Date',
                'Billable Nights',
                'Rooms Requested',
                'Status',
                'Tariff / Night (INR)',
                'Grand Total (INR)',
                'Paid Amount (INR)',
                'Balance Due (INR)',
            ]);

            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->booking_reference,
                    $b->applicant?->name,
                    $b->applicant?->department,
                    $b->primaryGuest?->name,
                    $b->guestHouse?->name,
                    $b->roomType?->name,
                    $b->arrival_date?->format('Y-m-d'),
                    $b->departure_date?->format('Y-m-d'),
                    $b->billable_nights,
                    $b->rooms_requested,
                    $b->status,
                    $b->applied_tariff_per_night,
                    $b->grand_total,
                    $b->paid_amount,
                    $b->balance_amount,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

