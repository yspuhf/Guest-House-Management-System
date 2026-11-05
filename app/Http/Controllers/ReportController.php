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
    /**
     * Standard Bookings Report
     */
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

    /**
     * 1. Daily Booking Report
     * Displays total bookings made for each day, detailed booking ledger, date-wise filtering, and statistics.
     */
    public function dailyReport(Request $request)
    {
        $targetDate = $request->filled('date') ? Carbon::parse($request->date)->toDateString() : Carbon::today()->toDateString();

        $query = Booking::with(['applicant', 'guestHouse', 'roomType', 'guestType', 'bookingRooms.room', 'primaryGuest'])
            ->whereDate('created_at', $targetDate);

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', $request->guest_house_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->get();

        // Calculate Daily Booking Statistics
        $totalBookings = $bookings->count();
        $approvedBookings = $bookings->where('status', 'Approved')->count();
        $pendingBookings = $bookings->whereIn('status', ['Submitted', 'Verification Pending', 'Approval Pending'])->count();
        $cancelledBookings = $bookings->where('status', 'Cancelled')->count();
        $checkedInGuests = $bookings->where('status', 'Checked In')->count();
        $grossRevenue = $bookings->sum('grand_total');

        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportDailyCsv($bookings, $targetDate);
        }

        $guestHouses = GuestHouse::where('is_active', true)->get();

        return view('reports.daily', compact(
            'bookings',
            'targetDate',
            'totalBookings',
            'approvedBookings',
            'pendingBookings',
            'cancelledBookings',
            'checkedInGuests',
            'grossRevenue',
            'guestHouses'
        ));
    }

    /**
     * 4. Monthly Report
     * Covers bookings received, approved/pending/cancelled, check-ins/outs, occupancy, dining/stay charges.
     */
    public function monthlyReport(Request $request)
    {
        $month = (int) $request->input('month', date('n'));
        $year = (int) $request->input('year', date('Y'));
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        $query = Booking::with(['applicant', 'guestHouse', 'roomType', 'guestType', 'bookingRooms.room', 'primaryGuest'])
            ->whereBetween('arrival_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', $request->guest_house_id);
        }

        $bookings = $query->get();

        // Statistics
        $totalReceived = $bookings->count();
        $approvedCount = $bookings->where('status', 'Approved')->count();
        $pendingCount = $bookings->whereIn('status', ['Submitted', 'Verification Pending', 'Approval Pending'])->count();
        $cancelledCount = $bookings->where('status', 'Cancelled')->count();
        $checkInsCount = $bookings->whereIn('status', ['Checked In', 'Checked Out'])->count();
        $checkOutsCount = $bookings->where('status', 'Checked Out')->count();

        // Invoices for the month
        $invoices = Invoice::whereBetween('invoice_date', [$startDate->toDateString(), $endDate->toDateString()])->get();
        $stayCharges = $invoices->sum('stay_charges_subtotal');
        if ($stayCharges == 0) {
            $stayCharges = $invoices->sum('subtotal');
        }
        $diningCharges = $invoices->sum('dining_charges_subtotal');
        if ($diningCharges == 0) {
            $diningCharges = $invoices->sum('additional_charges');
        }
        $totalCollection = $invoices->sum('paid_amount');
        $totalOutstanding = $invoices->sum('balance_due');

        // Total available room nights in month
        $totalRooms = Room::where('is_active', true)->count();
        $daysInMonth = $startDate->daysInMonth;
        $maxRoomNights = max(1, $totalRooms * $daysInMonth);
        $occupiedRoomNights = $bookings->whereIn('status', ['Approved', 'Room Allocated', 'Checked In', 'Checked Out'])->sum('billable_nights');
        $occupancyRate = round(($occupiedRoomNights / $maxRoomNights) * 100, 1);

        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportMonthlyCsv($bookings, $month, $year, $stayCharges, $diningCharges, $totalCollection, $totalOutstanding, $occupancyRate);
        }

        $guestHouses = GuestHouse::where('is_active', true)->get();

        return view('reports.monthly', compact(
            'month',
            'year',
            'bookings',
            'totalReceived',
            'approvedCount',
            'pendingCount',
            'cancelledCount',
            'checkInsCount',
            'checkOutsCount',
            'occupancyRate',
            'occupiedRoomNights',
            'stayCharges',
            'diningCharges',
            'totalCollection',
            'totalOutstanding',
            'guestHouses'
        ));
    }

    /**
     * 4. Annual Report (Financial Year April to March)
     * Year-wise stats, month-wise comparison, annual occupancy, dining & stay revenue.
     */
    public function annualReport(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $fyStart = Carbon::create($year, 4, 1)->startOfDay();
        $fyEnd = Carbon::create($year + 1, 3, 31)->endOfDay();

        $bookings = Booking::whereBetween('arrival_date', [$fyStart->toDateString(), $fyEnd->toDateString()])->get();
        $invoices = Invoice::whereBetween('invoice_date', [$fyStart->toDateString(), $fyEnd->toDateString()])->get();

        $totalBookings = $bookings->count();
        $totalStayRevenue = $invoices->sum('stay_charges_subtotal') ?: $invoices->sum('subtotal');
        $totalDiningRevenue = $invoices->sum('dining_charges_subtotal') ?: $invoices->sum('additional_charges');
        $totalCollection = $invoices->sum('paid_amount');
        $totalOutstanding = $invoices->sum('balance_due');

        // Month-wise breakdown (12 months from April to March)
        $monthlyBreakdown = [];
        for ($m = 4; $m <= 15; $m++) {
            $curMonth = ($m > 12) ? ($m - 12) : $m;
            $curYear = ($m > 12) ? ($year + 1) : $year;
            $mStart = Carbon::create($curYear, $curMonth, 1)->startOfMonth();
            $mEnd = (clone $mStart)->endOfMonth();

            $mBookings = $bookings->filter(fn($b) => $b->arrival_date >= $mStart && $b->arrival_date <= $mEnd);
            $mInvoices = $invoices->filter(fn($i) => $i->invoice_date >= $mStart->toDateString() && $i->invoice_date <= $mEnd->toDateString());

            $monthlyBreakdown[] = [
                'month_name' => $mStart->format('F Y'),
                'bookings_count' => $mBookings->count(),
                'stay_revenue' => $mInvoices->sum('stay_charges_subtotal') ?: $mInvoices->sum('subtotal'),
                'dining_revenue' => $mInvoices->sum('dining_charges_subtotal') ?: $mInvoices->sum('additional_charges'),
                'total_collection' => $mInvoices->sum('paid_amount'),
                'outstanding' => $mInvoices->sum('balance_due'),
            ];
        }

        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportAnnualCsv($monthlyBreakdown, $year, $totalBookings, $totalStayRevenue, $totalDiningRevenue, $totalCollection, $totalOutstanding);
        }

        return view('reports.annual', compact(
            'year',
            'totalBookings',
            'totalStayRevenue',
            'totalDiningRevenue',
            'totalCollection',
            'totalOutstanding',
            'monthlyBreakdown'
        ));
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

    protected function exportDailyCsv($bookings, string $targetDate): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"daily_booking_report_{$targetDate}.csv\"",
        ];

        return new StreamedResponse(function () use ($bookings, $targetDate) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ['Dr. Y.S. Parmar UHF Nauni - Daily Booking Report for ' . $targetDate]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'Booking Reference',
                'Guest Name',
                'Booking Date',
                'Check-In Date',
                'Check-Out Date',
                'Allotted Room',
                'Room Class',
                'Booking Status',
                'Payment Status',
                'Stay Charges (₹)',
                'Dining Charges (₹)',
                'Total Amount (₹)'
            ]);

            foreach ($bookings as $b) {
                $allotted = $b->bookingRooms->first()?->room?->room_number ?? 'Not Allotted';
                fputcsv($handle, [
                    $b->booking_reference,
                    $b->primaryGuest?->name ?? $b->applicant?->name ?? 'N/A',
                    $b->created_at->format('Y-m-d'),
                    $b->arrival_date?->format('Y-m-d'),
                    $b->departure_date?->format('Y-m-d'),
                    $allotted,
                    $b->roomType?->name ?? 'Standard',
                    $b->status,
                    $b->payment_status ?? 'Unpaid',
                    $b->total_room_charges ?? $b->grand_total,
                    $b->total_other_charges ?? 0.00,
                    $b->grand_total,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function exportMonthlyCsv($bookings, int $month, int $year, float $stay, float $dining, float $col, float $out, float $occ): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"monthly_report_{$year}_{$month}.csv\"",
        ];

        return new StreamedResponse(function () use ($bookings, $month, $year, $stay, $dining, $col, $out, $occ) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ["Dr. Y.S. Parmar UHF Nauni - Monthly Comprehensive Report ({$month}/{$year})"]);
            fputcsv($handle, ['Room Occupancy Rate: ' . $occ . '%']);
            fputcsv($handle, ['Stay Revenue (Non-GST): ₹' . number_format($stay, 2)]);
            fputcsv($handle, ['Dining Revenue (Non-GST): ₹' . number_format($dining, 2)]);
            fputcsv($handle, ['Total Collected: ₹' . number_format($col, 2)]);
            fputcsv($handle, ['Outstanding Balance: ₹' . number_format($out, 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['Booking Reference', 'Guest Name', 'Arrival', 'Departure', 'Nights', 'Status', 'Grand Total (₹)']);

            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->booking_reference,
                    $b->primaryGuest?->name ?? $b->applicant?->name,
                    $b->arrival_date?->format('Y-m-d'),
                    $b->departure_date?->format('Y-m-d'),
                    $b->billable_nights,
                    $b->status,
                    $b->grand_total
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function exportAnnualCsv(array $breakdown, int $year, int $totalB, float $stay, float $dining, float $col, float $out): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"annual_financial_report_FY_{$year}.csv\"",
        ];

        return new StreamedResponse(function () use ($breakdown, $year, $totalB, $stay, $dining, $col, $out) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ["Dr. Y.S. Parmar UHF Nauni - Annual Financial Report (FY {$year}-" . ($year + 1) . ")"]);
            fputcsv($handle, ['Total Bookings: ' . $totalB]);
            fputcsv($handle, ['Total Stay Revenue (Non-GST): ₹' . number_format($stay, 2)]);
            fputcsv($handle, ['Total Dining Revenue (Non-GST): ₹' . number_format($dining, 2)]);
            fputcsv($handle, ['Gross Collections: ₹' . number_format($col, 2)]);
            fputcsv($handle, ['Net Outstanding: ₹' . number_format($out, 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['Financial Month', 'Bookings Count', 'Stay Revenue (₹)', 'Dining Revenue (₹)', 'Collections (₹)', 'Outstanding (₹)']);

            foreach ($breakdown as $row) {
                fputcsv($handle, [
                    $row['month_name'],
                    $row['bookings_count'],
                    $row['stay_revenue'],
                    $row['dining_revenue'],
                    $row['total_collection'],
                    $row['outstanding']
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function exportBookingsCsv($bookings): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="university_guest_house_bookings_' . date('Ymd_His') . '.csv"',
        ];

        return new StreamedResponse(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
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
