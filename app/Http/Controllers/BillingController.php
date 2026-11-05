<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RefundOrAdjustment;
use App\Services\AuditService;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    protected BillingService $billingService;
    protected AuditService $auditService;

    public function __construct(BillingService $billingService, AuditService $auditService)
    {
        $this->billingService = $billingService;
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['booking.applicant', 'booking.guestHouse', 'payments', 'generatedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', "%{$request->invoice_number}%");
        }

        $invoices = $query->latest()->paginate(15);

        return view('billing.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['booking.applicant', 'booking.guestHouse', 'booking.guests', 'booking.bookingRooms.room', 'items', 'payments.collectedBy', 'refunds']);
        return view('billing.invoice', compact('invoice'));
    }

    public function generateStayInvoice(Booking $booking)
    {
        try {
            $invoice = $this->billingService->generateStayInvoice($booking, Auth::user());
            return redirect()->route('billing.invoice', $invoice)->with('success', "Non-GST Stay Charges Invoice {$invoice->invoice_number} generated successfully for checked-in guest.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Stay invoice generation failed: ' . $e->getMessage());
        }
    }

    public function generateDiningInvoice(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            $invoice = $this->billingService->generateDiningInvoice($booking, Auth::user(), $validated['items']);
            return redirect()->route('billing.invoice', $invoice)->with('success', "Non-GST Dining Charges Invoice {$invoice->invoice_number} generated successfully.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Dining invoice generation failed: ' . $e->getMessage());
        }
    }

    public function recordPayment(PaymentRequest $request, Invoice $invoice)
    {
        try {
            $payment = $this->billingService->recordPayment($invoice, $request->validated(), Auth::user());
            return redirect()->route('billing.receipt', $payment)->with('success', "Payment of ₹" . number_format($payment->amount, 2) . " received against Receipt #{$payment->receipt_number}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function showReceipt(Payment $payment)
    {
        $payment->load(['invoice.items', 'booking.applicant', 'booking.guestHouse', 'booking.primaryGuest', 'collectedBy']);
        return view('billing.receipt', compact('payment'));
    }

    public function refund(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|min:5|max:500',
            'payment_id' => 'nullable|exists:payments,id',
            'type' => 'required|in:Refund,Adjustment,Waiver',
        ]);

        $refund = RefundOrAdjustment::create([
            'invoice_id' => $invoice->id,
            'payment_id' => $validated['payment_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'authorized_by_id' => Auth::id(),
            'reference_number' => 'REF-' . time(),
        ]);

        $invoice->paid_amount = max(0.00, round((float)$invoice->paid_amount - (float)$validated['amount'], 2));
        $invoice->balance_due = round((float)$invoice->grand_total - (float)$invoice->paid_amount, 2);
        if ($invoice->paid_amount <= 0) {
            $invoice->status = 'Refunded';
        }
        $invoice->save();

        $this->auditService->log(Auth::user(), 'Authorized Refund/Adjustment', 'Finance', RefundOrAdjustment::class, $refund->id);

        return back()->with('success', "{$validated['type']} of ₹" . number_format($validated['amount'], 2) . " processed successfully.");
    }
}
