<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\GuestTypeTariff;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\RefundOrAdjustment;
use App\Models\SystemSetting;
use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    protected AuditService $auditService;
    protected NotificationService $notificationService;

    public function __construct(AuditService $auditService, NotificationService $notificationService)
    {
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    /**
     * Compute stay tariff and estimated totals for a booking.
     * Uses category-wise tariff under Notification No. UHF/EO-55/2025/- 1615-40.
     * Institutional university guesthouse stays are non-GST exempt.
     */
    public function computeBookingCharges(Booking $booking): array
    {
        $nights = max(1, Carbon::parse($booking->arrival_date)->diffInDays(Carbon::parse($booking->departure_date)));
        $roomsCount = max(1, $booking->rooms_requested);

        // Check Category-wise tariff
        $rate = 0.00;
        $tariffRecord = GuestTypeTariff::where('guest_type_id', $booking->guest_type_id)
            ->where('room_type_id', $booking->room_type_id)
            ->where('is_active', true)
            ->first();

        if ($tariffRecord) {
            $rate = (float) $tariffRecord->rate_per_night;
        } else {
            $rate = (float) ($booking->roomType?->default_tariff ?? 0.00);
        }

        $roomCharges = round($rate * $nights * $roomsCount, 2);

        // Under University Institutional Rules & Notification 1615-40, guest house stays are Non-GST
        $isExempt = true;
        $taxAmount = 0.00;

        $grandTotal = round($roomCharges + $taxAmount, 2);

        return [
            'nights' => $nights,
            'rate_per_night' => $rate,
            'room_charges' => $roomCharges,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'is_exempt' => $isExempt,
        ];
    }

    /**
     * Generate or update an invoice for a booking (Consolidated, Stay, or Dining).
     * Strictly Non-GST compliant as per Institutional notification.
     */
    public function generateInvoice(Booking $booking, User $actor, array $additionalCharges = [], string $invoiceType = 'consolidated'): Invoice
    {
        return DB::transaction(function () use ($booking, $actor, $additionalCharges, $invoiceType) {
            $charges = $this->computeBookingCharges($booking);

            $invYear = date('Y');
            $latest = Invoice::whereYear('created_at', $invYear)->count() + 1;
            $prefix = ($invoiceType === 'dining') ? 'DIN' : (($invoiceType === 'stay') ? 'STY' : 'INV');
            $invNumber = sprintf('%s-%s-%04d', $prefix, $invYear, $latest);

            $extraTotal = 0.00;
            foreach ($additionalCharges as $item) {
                $extraTotal += (float) ($item['amount'] ?? 0.00);
            }

            $subtotal = ($invoiceType === 'dining') ? 0.00 : $charges['room_charges'];
            $diningTotal = ($invoiceType === 'stay') ? 0.00 : $extraTotal;
            $grandTotal = round($subtotal + $diningTotal, 2);

            $primaryGuest = $booking->primaryGuest;

            $invoice = Invoice::create([
                'invoice_number' => $invNumber,
                'booking_id' => $booking->id,
                'invoice_type' => $invoiceType,
                'billed_to_guest_id' => $primaryGuest?->id,
                'billed_guest_name' => $primaryGuest?->name ?? $booking->applicant?->name ?? 'Guest',
                'invoice_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'tax_total' => 0.00,
                'additional_charges' => $diningTotal,
                'discount_or_waiver' => 0.00,
                'stay_charges_subtotal' => $subtotal,
                'dining_charges_subtotal' => $diningTotal,
                'is_non_gst' => true,
                'tax_exemption_clause' => 'Notification No. UHF/EO-55/2025/- 1615-40 (Public Educational Facility Exemption)',
                'grand_total' => $grandTotal,
                'paid_amount' => 0.00,
                'balance_due' => $grandTotal,
                'status' => 'Unpaid',
                'is_finalized' => false,
                'generated_by_id' => $actor->id,
            ]);

            // Add room charges line item if stay or consolidated
            if ($invoiceType !== 'dining') {
                $allottedRoom = $booking->bookingRooms()->with('room')->first()?->room;
                $roomDesc = $allottedRoom ? "Room {$allottedRoom->room_number} ({$booking->roomType?->name})" : ($booking->roomType?->name ?? 'Guest Set');
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_description' => "Stay Charges: {$roomDesc} ({$charges['nights']} nights x {$booking->rooms_requested} room(s) @ ₹{$charges['rate_per_night']})",
                    'item_type' => 'room_charge',
                    'unit_rate' => $charges['rate_per_night'],
                    'quantity' => $charges['nights'] * $booking->rooms_requested,
                    'line_total' => $charges['room_charges'],
                    'tax_rate_applied' => 0.00,
                ]);
            }

            // Add dining items if dining or consolidated
            if ($invoiceType !== 'stay') {
                foreach ($additionalCharges as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_description' => $item['description'] ?? 'Dining / Mess Service',
                        'item_type' => 'meal',
                        'unit_rate' => (float)($item['rate'] ?? $item['amount']),
                        'quantity' => (int)($item['quantity'] ?? 1),
                        'line_total' => (float)$item['amount'],
                        'tax_rate_applied' => 0.00,
                    ]);
                }
            }

            $this->auditService->log($actor, "Generated {$invoiceType} Invoice", 'Finance', Invoice::class, $invoice->id, null, [
                'invoice_number' => $invNumber,
                'invoice_type' => $invoiceType,
                'grand_total' => $grandTotal,
                'is_non_gst' => true,
            ]);

            return $invoice;
        });
    }

    /**
     * Generate Stay Charges Invoice specifically for the checked-in resident (Non-GST).
     */
    public function generateStayInvoice(Booking $booking, User $actor, ?int $guestId = null): Invoice
    {
        return $this->generateInvoice($booking, $actor, [], 'stay');
    }

    /**
     * Generate Dining Charges Invoice (Non-GST).
     */
    public function generateDiningInvoice(Booking $booking, User $actor, array $diningItems, ?int $guestId = null): Invoice
    {
        return $this->generateInvoice($booking, $actor, $diningItems, 'dining');
    }

    /**
     * Record a payment against an invoice and booking.
     */
    public function recordPayment(Invoice $invoice, array $data, User $actor): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $actor) {
            $amount = round((float) $data['amount'], 2);
            if ($amount <= 0) {
                throw new \InvalidArgumentException('Payment amount must be greater than zero.');
            }

            $booking = $invoice->booking;

            $recYear = date('Y');
            $latest = Payment::whereYear('created_at', $recYear)->count() + 1;
            $receiptNumber = sprintf('REC-%s-%04d', $recYear, $latest);

            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'invoice_id' => $invoice->id,
                'booking_id' => $booking->id,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'status' => 'Successful',
                'notes' => $data['notes'] ?? null,
                'collected_by_id' => $actor->id,
            ]);

            $newPaid = round((float)$invoice->paid_amount + $amount, 2);
            $newBalance = max(0.00, round((float)$invoice->grand_total - $newPaid, 2));
            $newStatus = ($newBalance <= 0) ? 'Paid' : 'Partially Paid';

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
                'status' => $newStatus,
                'is_finalized' => true,
            ]);

            $bookingPaid = round((float)$booking->paid_amount + $amount, 2);
            $bookingBalance = max(0.00, round((float)$booking->grand_total - $bookingPaid, 2));
            $paymentStatus = ($bookingBalance <= 0) ? 'paid' : 'partially_paid';

            $booking->update([
                'paid_amount' => $bookingPaid,
                'balance_amount' => $bookingBalance,
                'payment_status' => $paymentStatus,
            ]);

            $this->auditService->log($actor, 'Recorded Payment', 'Finance', Payment::class, $payment->id, null, [
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'method' => $payment->payment_method,
            ]);

            $this->notificationService->notify($booking, 'invoice_settled', [
                'RECEIPT_NUM' => $receiptNumber,
                'AMOUNT_PAID' => number_format($amount, 2),
                'PAYMENT_METHOD' => $payment->payment_method,
            ]);

            return $payment;
        });
    }

    /**
     * Check if payment stage rule is met for current action.
     */
    public function validatePaymentStage(Booking $booking, string $action): bool
    {
        $configuredStage = SystemSetting::get('payment_stage', 'check_out');

        if ($configuredStage === 'approval' && $action === 'allocate_room') {
            return $booking->payment_status === 'paid';
        }

        if ($configuredStage === 'check_in' && $action === 'check_in') {
            return $booking->payment_status === 'paid';
        }

        return true;
    }
}
