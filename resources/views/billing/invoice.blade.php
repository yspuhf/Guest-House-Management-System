@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Action Ribbon -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer me-1"></i> Print Invoice
                </button>
                @if($invoice->balance_due > 0 && auth()->user()->hasRole(['accounts_finance', 'guest_house_incharge', 'system_admin']))
                    <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-cash-stack me-1"></i> Record Payment
                    </button>
                @endif
            </div>
        </div>

        <!-- Official University Invoice Card -->
        <div class="card shadow-sm border p-4 p-md-5 bg-white">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h4 class="fw-bold text-success mb-1">Dr. Y.S. Parmar University of Horticulture & Forestry</h4>
                    <div class="small text-muted">Nauni, Solan - 173230 (H.P.) &bull; Comptroller & Finance Division</div>
                    <div class="small text-muted">Guest House Management & Accommodation Cell</div>
                </div>
                <div class="text-end">
                    <h3 class="fw-bold mb-0 text-dark">INVOICE</h3>
                    <div class="fs-5 text-primary fw-semibold">{{ $invoice->invoice_number }}</div>
                    <div class="small text-muted">Date: <strong>{{ $invoice->invoice_date->format('d F Y') }}</strong></div>
                    <span class="badge @if($invoice->status === 'Paid') bg-success @elseif($invoice->status === 'Unpaid') bg-danger @else bg-warning text-dark @endif mt-1">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>
            </div>

            <!-- Billed To & Booking Info -->
            <div class="row g-4 mb-4 pb-3 border-bottom">
                <div class="col-sm-6">
                    <h6 class="text-uppercase text-muted fw-bold small">Billed To (Guest & Applicant):</h6>
                    <div class="fs-6 fw-bold text-dark">{{ $invoice->booking->primaryGuest?->name ?? 'Guest' }}</div>
                    <div class="small text-muted">{{ $invoice->booking->primaryGuest?->designation }}</div>
                    <div class="small text-muted">Mobile: {{ $invoice->booking->primaryGuest?->phone }}</div>
                    <div class="small text-muted mt-2">
                        Sponsoring Faculty: <strong>{{ $invoice->booking->applicant->name }}</strong> ({{ $invoice->booking->applicant->department }})
                    </div>
                </div>

                <div class="col-sm-6 text-sm-end">
                    <h6 class="text-uppercase text-muted fw-bold small">Stay Details:</h6>
                    <div>Booking Ref: <strong><a href="{{ route('bookings.show', $invoice->booking) }}">{{ $invoice->booking->booking_reference }}</a></strong></div>
                    <div>Property: <strong>{{ $invoice->booking->guestHouse->name }}</strong></div>
                    <div>Stay Interval: <strong>{{ $invoice->booking->arrival_date->format('d M') }} &rarr; {{ $invoice->booking->departure_date->format('d M Y') }}</strong> ({{ $invoice->booking->billable_nights }} nights)</div>
                    <div>Category: <strong>{{ $invoice->booking->guestType->name }}</strong></div>
                    @if($invoice->booking->expenditureHead)
                        <div class="text-success small mt-1">Charged to: <strong>{{ $invoice->booking->expenditureHead->name }}</strong></div>
                    @endif
                </div>
            </div>

            <!-- Itemized Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Item Type</th>
                            <th class="text-end">Unit Rate (INR)</th>
                            <th class="text-center">Qty / Nights</th>
                            <th class="text-end">Amount (INR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="fw-semibold">{{ $item->item_description }}</td>
                            <td><span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $item->item_type) }}</span></td>
                            <td class="text-end">₹{{ number_format($item->unit_rate, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & Balances -->
            <div class="row justify-content-end mb-4">
                <div class="col-md-5">
                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Room Accommodation Charges:</span>
                            <span class="fw-semibold">₹{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Total Taxes (CGST + SGST):</span>
                            <span>₹{{ number_format($invoice->tax_total, 2) }}</span>
                        </div>
                        @if($invoice->additional_charges > 0)
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Incidentals / Dining Charges:</span>
                            <span>₹{{ number_format($invoice->additional_charges, 2) }}</span>
                        </div>
                        @endif
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Grand Total Payable:</span>
                            <span class="fw-bold fs-5 text-dark">₹{{ number_format($invoice->grand_total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Total Amount Collected:</span>
                            <span class="text-success fw-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-danger">Balance Due:</span>
                            <span class="fw-bold fs-5 text-danger">₹{{ number_format($invoice->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Ledger for This Invoice -->
            @if($invoice->payments->count() > 0)
            <div class="border-top pt-4 mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 text-success me-2"></i> Official Receipts & Payments Received</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Receipt #</th>
                                <th>Payment Date</th>
                                <th>Method</th>
                                <th>Transaction Ref</th>
                                <th>Collected By</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Receipt Voucher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $pay)
                            <tr>
                                <td class="fw-bold text-primary">{{ $pay->receipt_number }}</td>
                                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                <td><span class="badge bg-secondary">{{ $pay->payment_method }}</span></td>
                                <td>{{ $pay->transaction_reference ?? 'Counter Cash' }}</td>
                                <td>{{ $pay->collectedBy->name }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($pay->amount, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('billing.receipt', $pay) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-printer"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Cashier / Authorized Signatory Footer -->
            <div class="row text-center mt-5 pt-4 border-top">
                <div class="col-6 text-start small text-muted">
                    Generated by: <strong>{{ $invoice->generatedBy->name }}</strong><br>
                    Printed on: {{ date('d M Y, h:i A') }}
                </div>
                <div class="col-6">
                    <div style="border-top: 1px dashed #333; width: 75%; margin: 0 auto; padding-top: 5px;">
                        <strong>Authorized Cashier / Accounts Section</strong><br>
                        <small class="text-muted">Dr. Y.S. Parmar UHF Nauni</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal to Record Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('billing.payment', $invoice) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i> Receive & Record Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small">
                        Invoice: <strong>{{ $invoice->invoice_number }}</strong> &bull; Outstanding Balance: <strong class="text-danger">₹{{ number_format($invoice->balance_due, 2) }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Amount to Collect (INR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" min="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Instrument / Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash (Counter Collection)</option>
                            <option value="UPI / QR">UPI / QR Code (BHIM / GooglePay / PhonePe)</option>
                            <option value="POS / Card">POS Debit / Credit Card</option>
                            <option value="Online (Payment Gateway)">Online (Payment Gateway Confirmation)</option>
                            <option value="Demand Draft / Cheque">Demand Draft / Bank Cheque</option>
                            <option value="Book Transfer / University Account">Book Transfer / Departmental Account</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bank / Transaction Reference (If Applicable)</label>
                        <input type="text" name="transaction_reference" class="form-control" placeholder="UPI Ref / Cheque No / Transaction ID">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cashier Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Received at front desk against check-out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Issue Official University Receipt</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

