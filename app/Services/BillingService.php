<?php

namespace App\Services;

use App\Models\Booking;
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
     * Uses category-wise tariff if configured, otherwise falls back to room/room_type default.
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

        // Taxes: check active taxes
        $taxAmount = 0.00;
        $activeTaxes = Tax::where('is_active', true)->get();

        // If official Category A or under exemption threshold, tax may be 0
        $isExempt = ($booking->guestType?->code === 'CAT-A') || ($rate <= 1000.00);

        if (!$isExempt) {
            foreach ($activeTaxes as $tax) {
                if ($tax->code !== 'GST-EXEMPT') {
                    if ($tax->calculation_method === 'percentage') {
                        $taxAmount += round(($roomCharges * (float)$tax->rate) / 100, 2);
                    } else {
                        $taxAmount += (float)$tax->rate;
                    }
                }
            }
        }

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
     * Generate or update an invoice for a booking.
     * Locks line items with fixed prices so future tariff/tax changes never modify historic invoices.
     */
    public function generateInvoice(Booking $booking, User $actor, array $additionalCharges = []): Invoice
    {
        return DB::transaction(function () use ($booking, $actor, $additionalCharges) {
            $charges = $this->computeBookingCharges($booking);

            // Generate unique invoice number: INV-YYYY-XXXX
            $invYear = date('Y');
            $latest = Invoice::whereYear('created_at', $invYear)->count() + 1;
            $invNumber = sprintf('INV-%s-%04d', $invYear, $latest);

            $extraTotal = 0.00;
            foreach ($additionalCharges as $item) {
                $extraTotal += (float) ($item['amount'] ?? 0.00);
            }

            $grandTotal = round($charges['room_charges'] + $charges['tax_amount'] + $extraTotal, 2);

            $invoice = Invoice::create([
                'invoice_number' => $invNumber,
                'booking_id' => $booking->id,
                'invoice_date' => now()->toDateString(),
                'subtotal' => $charges['room_charges'],
                'tax_total' => $charges['tax_amount'],
                'additional_charges' => $extraTotal,
                'discount_or_waiver' => 0.00,
                'grand_total' => $grandTotal,
                'paid_amount' => $booking->paid_amount,
                'balance_due' => round($grandTotal - $booking->paid_amount, 2),
                'status' => ($booking->paid_amount >= $grandTotal) ? 'Paid' : (($booking->paid_amount > 0) ? 'Partially Paid' : 'Unpaid'),
                'is_finalized' => false,
                'generated_by_id' => $actor->id,
            ]);

            // Add room charges line item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_description' => "Room Charges: {$booking->roomType?->name} ({$charges['nights']} nights x {$booking->rooms_requested} room(s) @ ₹{$charges['rate_per_night']})",
                'item_type' => 'room_charge',
                'unit_rate' => $charges['rate_per_night'],
                'quantity' => $charges['nights'] * $booking->rooms_requested,
                'line_total' => $charges['room_charges'],
                'tax_rate_applied' => 0.00,
            ]);

            // Add tax items
            if (!$charges['is_exempt']) {
                $taxes = Tax::where('is_active', true)->where('code', '!=', 'GST-EXEMPT')->get();
                foreach ($taxes as $tax) {
                    $taxVal = round(($charges['room_charges'] * (float)$tax->rate) / 100, 2);
                    if ($taxVal > 0) {
                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'item_description' => "{$tax->name} ({$tax->rate}%)",
                            'item_type' => 'tax',
                            'unit_rate' => $taxVal,
                            'quantity' => 1,
                            'line_total' => $taxVal,
                            'tax_id' => $tax->id,
                            'tax_rate_applied' => $tax->rate,
                        ]);
                    }
                }
            }

            // Add additional incidental items (meals, damages, etc.)
            foreach ($additionalCharges as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_description' => $item['description'] ?? 'Incidental Charge',
                    'item_type' => $item['type'] ?? 'meal',
                    'unit_rate' => (float)$item['amount'],
                    'quantity' => 1,
                    'line_total' => (float)$item['amount'],
                    'tax_rate_applied' => 0.00,
                ]);
            }

            // Sync totals back to booking
            $booking->update([
                'applied_tariff_per_night' => $charges['rate_per_night'],
                'total_room_charges' => $charges['room_charges'],
                'total_tax_amount' => $charges['tax_amount'],
                'total_other_charges' => $extraTotal,
                'grand_total' => $grandTotal,
                'balance_amount' => round($grandTotal - $booking->paid_amount, 2),
            ]);

            $this->auditService->log($actor, 'Generated Invoice', 'Finance', Invoice::class, $invoice->id, null, ['invoice_number' => $invNumber, 'grand_total' => $grandTotal]);

            return $invoice;
        });
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

            // Generate receipt number: REC-YYYY-XXXX
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

            // Update Invoice
            $newPaid = round((float)$invoice->paid_amount + $amount, 2);
            $newBalance = max(0.00, round((float)$invoice->grand_total - $newPaid, 2));
            $newStatus = ($newBalance <= 0) ? 'Paid' : 'Partially Paid';

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
                'status' => $newStatus,
                'is_finalized' => true,
            ]);

            // Update Booking
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

            // Notify user
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
     * Stages: 'approval', 'check_in', 'check_out'
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

