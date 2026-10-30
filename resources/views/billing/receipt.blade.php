<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Cash Receipt - {{ $payment->receipt_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #111; background: #fff; }
        .receipt-border { border: 2px dashed #1b4332; padding: 25px; border-radius: 6px; }
        .univ-header { border-bottom: 2px double #1b4332; padding-bottom: 12px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">
    <div class="container" style="max-width: 750px;">
        <div class="no-print text-end mb-3">
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Print Receipt</button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">Close</button>
        </div>

        <div class="receipt-border">
            <!-- University Header -->
            <div class="text-center univ-header">
                <h4 class="fw-bold mb-1 text-uppercase" style="color: #1b4332;">Dr. Yashwant Singh Parmar</h4>
                <h5 class="fw-bold mb-1">University of Horticulture & Forestry</h5>
                <div class="small">Nauni, Solan - 173230, Himachal Pradesh &bull; Finance Branch</div>
                <div class="mt-2"><span class="badge bg-success text-white px-3 py-1 fs-6">Official Guest House Cash Receipt</span></div>
            </div>

            <!-- Receipt Meta Row -->
            <div class="d-flex justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <strong>Receipt Number:</strong> <span class="fs-5 fw-bold text-success">{{ $payment->receipt_number }}</span><br>
                    <strong>Invoice Reference:</strong> {{ $payment->invoice->invoice_number }}
                </div>
                <div class="text-end">
                    <strong>Date of Receipt:</strong> {{ $payment->payment_date->format('d F Y') }}<br>
                    <strong>Booking Ref:</strong> {{ $payment->booking->booking_reference }}
                </div>
            </div>

            <!-- Received From Particulars -->
            <div class="mb-4">
                <p class="fs-6 lh-lg mb-2">
                    Received with thanks from <strong>{{ $payment->booking->primaryGuest?->name ?? 'Guest' }}</strong>
                    @if($payment->booking->applicant)
                        (Sponsored by <strong>{{ $payment->booking->applicant->name }}</strong>, {{ $payment->booking->applicant->department }})
                    @endif
                    the sum of <strong class="fs-5 text-dark">INR ₹{{ number_format($payment->amount, 2) }}</strong>
                    via <strong>{{ $payment->payment_method }}</strong>
                    @if($payment->transaction_reference)
                        (Ref / Txn ID: <code>{{ $payment->transaction_reference }}</code>)
                    @endif
                    towards Guest House Lodging & Boarding charges at <strong>{{ $payment->booking->guestHouse->name }}</strong>
                    for stay period from <strong>{{ $payment->booking->arrival_date->format('d M Y') }}</strong> to <strong>{{ $payment->booking->departure_date->format('d M Y') }}</strong>.
                </p>
            </div>

            <!-- Financial Settlement Box -->
            <div class="p-3 bg-light rounded border mb-4">
                <div class="row text-center">
                    <div class="col-4 border-end">
                        <small class="text-muted d-block">Total Billed</small>
                        <strong class="fs-6">₹{{ number_format($payment->invoice->grand_total, 2) }}</strong>
                    </div>
                    <div class="col-4 border-end">
                        <small class="text-muted d-block">Amount This Receipt</small>
                        <strong class="fs-6 text-success">₹{{ number_format($payment->amount, 2) }}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Remaining Balance</small>
                        <strong class="fs-6 @if($payment->invoice->balance_due > 0) text-danger @else text-success @endif">
                            ₹{{ number_format($payment->invoice->balance_due, 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($payment->notes)
                <div class="small text-muted mb-4 fst-italic">Note: {{ $payment->notes }}</div>
            @endif

            <!-- Signatures -->
            <div class="row text-center mt-5 pt-4">
                <div class="col-6">
                    <div style="border-top: 1px dashed #333; width: 75%; margin: 0 auto; padding-top: 5px;">
                        <strong>Guest Signature</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div style="border-top: 1px dashed #333; width: 75%; margin: 0 auto; padding-top: 5px;">
                        <strong>Cashier / Collector: {{ $payment->collectedBy->name }}</strong><br>
                        <small class="text-muted">Estate / Accounts Section, Dr. YSP UHF</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

