<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation Voucher - {{ $booking->booking_reference }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #111; background: #fff; }
        .voucher-border { border: 2px solid #1b4332; padding: 25px; border-radius: 6px; }
        .univ-header { border-bottom: 2px double #1b4332; padding-bottom: 12px; margin-bottom: 20px; }
        .watermark { position: absolute; opacity: 0.05; font-size: 8rem; font-weight: bold; transform: rotate(-35deg); top: 30%; left: 10%; pointer-events: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .voucher-border { border: 1px solid #000; }
        }
    </style>
</head>
<body class="p-4">
    <div class="container" style="max-width: 800px;">
        <div class="no-print text-end mb-3">
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Print / Save as PDF</button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">Close Window</button>
        </div>

        <div class="voucher-border position-relative">
            <div class="watermark">YSP UHF</div>

            <!-- University Official Header -->
            <div class="text-center univ-header">
                <h4 class="fw-bold mb-1 text-uppercase" style="color: #1b4332;">Dr. Yashwant Singh Parmar</h4>
                <h5 class="fw-bold mb-1">University of Horticulture & Forestry</h5>
                <div class="small">Nauni, Solan - 173230, Himachal Pradesh (India)</div>
                <div class="small fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">Office of the Guest House Incharge &bull; Estate Branch</div>
                <div class="mt-2"><span class="badge bg-dark text-white px-3 py-1 fs-6">Official Accommodation Voucher</span></div>
            </div>

            <!-- Reference and Date Row -->
            <div class="d-flex justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <strong>Voucher / Ref No:</strong> <span class="fs-5 fw-bold text-primary">{{ $booking->booking_reference }}</span><br>
                    <small class="text-muted">Status: <strong>{{ strtoupper($booking->status) }}</strong></small>
                </div>
                <div class="text-end">
                    <strong>Issue Date:</strong> {{ date('d F Y') }}<br>
                    <small class="text-muted">Payment Stage: <strong>{{ strtoupper($booking->payment_stage_applied) }}</strong></small>
                </div>
            </div>

            <!-- Guest & Sponsoring Details -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-uppercase border-bottom pb-1" style="font-size: 0.85rem;">Guest Information</h6>
                    <div><strong>Name:</strong> {{ $booking->primaryGuest?->name ?? 'Guest' }}</div>
                    <div><strong>Designation:</strong> {{ $booking->primaryGuest?->designation ?? 'N/A' }}</div>
                    <div><strong>Contact:</strong> {{ $booking->primaryGuest?->phone ?? 'N/A' }}</div>
                    <div><strong>Guest Category:</strong> {{ $booking->guestType->name }}</div>
                </div>

                <div class="col-6">
                    <h6 class="fw-bold text-uppercase border-bottom pb-1" style="font-size: 0.85rem;">Sponsoring Applicant</h6>
                    <div><strong>Applicant:</strong> {{ $booking->applicant->name }}</div>
                    <div><strong>Department:</strong> {{ $booking->applicant->department ?? 'General' }}</div>
                    <div><strong>Head of Account:</strong> {{ $booking->expenditureHead?->name ?? 'Self-Paid by Guest' }}</div>
                    <div><strong>Meal Plan:</strong> {{ $booking->meal_preference }}</div>
                </div>
            </div>

            <!-- Stay Reservation Particulars -->
            <table class="table table-bordered mb-4" style="border-color: #333;">
                <thead class="table-light">
                    <tr>
                        <th>Guest House</th>
                        <th>Allocated Room(s)</th>
                        <th>Check-In (Arrival)</th>
                        <th>Check-Out (Departure)</th>
                        <th>Billable Nights</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">{{ $booking->guestHouse->name }}</td>
                        <td class="fw-bold text-success fs-6">
                            @if($booking->bookingRooms->count() > 0)
                                {{ $booking->bookingRooms->map(fn($br) => 'Room ' . $br->room->room_number)->join(', ') }}
                            @else
                                Desk Allocation
                            @endif
                        </td>
                        <td>{{ $booking->arrival_date->format('d M Y') }} ({{ $booking->arrival_time }})</td>
                        <td>{{ $booking->departure_date->format('d M Y') }} ({{ $booking->departure_time }})</td>
                        <td class="text-center fw-bold">{{ $booking->billable_nights }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Financial Tariff Assessment -->
            <div class="row justify-content-end mb-4">
                <div class="col-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td>Room Charges ({{ $booking->billable_nights }} N &times; ₹{{ number_format($booking->applied_tariff_per_night, 2) }}):</td>
                            <td class="text-end fw-bold">₹{{ number_format($booking->total_room_charges, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Applicable Taxes (GST):</td>
                            <td class="text-end fw-bold">₹{{ number_format($booking->total_tax_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-6">Total Estimated Amount:</td>
                            <td class="text-end fw-bold fs-6">₹{{ number_format($booking->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Amount Paid:</td>
                            <td class="text-end text-success fw-bold">₹{{ number_format($booking->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top border-dark">
                            <td class="fw-bold">Outstanding Balance:</td>
                            <td class="text-end fw-bold text-danger">₹{{ number_format($booking->balance_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Important Rules & Instructions -->
            <div class="p-3 bg-light rounded small mb-5 border">
                <strong>Important University Instructions:</strong>
                <ol class="mb-0 ps-3 mt-1">
                    <li>Valid government photo identity proof must be produced by all guests at check-in.</li>
                    <li>Check-out time is strictly <strong>11:00 AM</strong>. Stay beyond scheduled check-out requires prior written permission from the Incharge.</li>
                    <li>Sub-letting of accommodation is strictly prohibited under University Ordinances.</li>
                    <li>Alcohol consumption and smoking are strictly forbidden within guest house premises.</li>
                </ol>
            </div>

            <!-- Signature Authorities -->
            <div class="row text-center mt-5 pt-4">
                <div class="col-6">
                    <div style="border-top: 1px dashed #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                        <strong>Guest Signature</strong><br>
                        <small class="text-muted">(To be signed on arrival)</small>
                    </div>
                </div>
                <div class="col-6">
                    <div style="border-top: 1px dashed #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                        <strong>Guest House Incharge / Caretaker</strong><br>
                        <small class="text-muted">Dr. Y.S. Parmar UHF Nauni</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

