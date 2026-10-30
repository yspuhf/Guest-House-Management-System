@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_reference)

@section('content')
<div class="row">
    <!-- Main Column: Dossier -->
    <div class="col-lg-8">
        <!-- Header Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold mb-0 text-dark">{{ $booking->booking_reference }}</h4>
                            @include('partials.status_badge', ['status' => $booking->status])
                        </div>
                        <small class="text-muted">Requested on {{ $booking->created_at->format('d M Y, h:i A') }}</small>
                    </div>

                    <div class="d-flex gap-2 mt-2 mt-sm-0">
                        <a href="{{ route('bookings.print', $booking) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-printer me-1"></i> Print Voucher
                        </a>
                        @if($booking->isDraft() || $booking->isReturned())
                            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit Requisition
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Action Controls Based on Role & Status -->
                <div class="bg-light p-3 rounded d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div>
                        <span class="small fw-bold text-secondary text-uppercase"><i class="bi bi-gear me-1"></i> Available Operations:</span>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <!-- Submit Draft -->
                        @if($booking->isDraft())
                            <form action="{{ route('bookings.submit', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-send-check me-1"></i> Submit for Scrutiny
                                </button>
                            </form>
                        @endif

                        <!-- Verifier Actions -->
                        @if(($booking->isSubmitted() || $booking->isVerificationPending()) && auth()->user()->hasRole(['verifier', 'system_admin']))
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#verifyModal">
                                <i class="bi bi-check-circle me-1"></i> Verify & Forward
                            </button>
                            <button type="button" class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#returnModal">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Return for Correction
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i> Reject Request
                            </button>
                        @endif

                        <!-- Approver Actions -->
                        @if($booking->isApprovalPending() && auth()->user()->hasRole(['approver', 'system_admin']))
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="bi bi-award me-1"></i> Sanction / Approve Request
                            </button>
                            <button type="button" class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#returnModal">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Return for Correction
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i> Reject Request
                            </button>
                        @endif

                        <!-- Incharge Allocation Action -->
                        @if($booking->isApproved() && auth()->user()->hasRole(['guest_house_incharge', 'guest_house_admin', 'system_admin']))
                            <a href="{{ route('allocations.form', $booking) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-door-open me-1"></i> Allocate Physical Rooms
                            </a>
                        @endif

                        <!-- Incharge Check-in Action -->
                        @if($booking->isAllocated() && auth()->user()->hasRole(['guest_house_incharge', 'system_admin']))
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#checkInModal">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Check-in Guest
                            </button>
                        @endif

                        <!-- Incharge Check-out Action -->
                        @if($booking->isCheckedIn() && auth()->user()->hasRole(['guest_house_incharge', 'system_admin']))
                            <button type="button" class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#checkOutModal">
                                <i class="bi bi-box-arrow-right me-1"></i> Check-out & Settle
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#extensionModal">
                                <i class="bi bi-calendar-plus me-1"></i> Request Extension
                            </button>
                        @endif

                        <!-- Self-Cancellation Action -->
                        @if($booking->canBeCancelled())
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-ban me-1"></i> Cancel Booking
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Dossier Detail Cards -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i> Requisition Dossier & Stay Specification</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted fw-bold small">Guest House & Room Preference</h6>
                        <div class="fs-5 fw-bold text-dark">{{ $booking->guestHouse->name }}</div>
                        <div class="text-muted small">{{ $booking->guestHouse->address }}</div>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark border">
                                Category: <strong>{{ $booking->roomType->name }}</strong>
                            </span>
                            <span class="badge bg-light text-dark border">
                                Rooms Requested: <strong>{{ $booking->rooms_requested }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted fw-bold small">Stay Duration & Timings</h6>
                        <div class="fs-6 fw-bold text-dark">
                            {{ $booking->arrival_date->format('l, d M Y') }} &rarr; {{ $booking->departure_date->format('l, d M Y') }}
                        </div>
                        <div class="text-muted small">
                            Check-in: <strong>{{ $booking->arrival_time }}</strong> &bull; Check-out: <strong>{{ $booking->departure_time }}</strong>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-moon me-1"></i> {{ $booking->billable_nights }} Billable Night(s) [Arrival to Departure)
                            </span>
                        </div>
                    </div>

                    <div class="col-md-12 border-top pt-3">
                        <h6 class="text-uppercase text-muted fw-bold small">Official Purpose of Visit</h6>
                        <p class="mb-0 text-dark bg-light p-3 rounded border">{{ $booking->purpose }}</p>
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <h6 class="text-uppercase text-muted fw-bold small">Dining Preference</h6>
                        <div><i class="bi bi-cup-hot text-secondary me-1"></i> {{ $booking->meal_preference }}</div>
                        @if($booking->special_requests)
                            <div class="mt-1 small text-muted"><strong>Special Requests:</strong> {{ $booking->special_requests }}</div>
                        @endif
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <h6 class="text-uppercase text-muted fw-bold small">Budget / Head of Account</h6>
                        @if($booking->expenditureHead)
                            <div class="fw-semibold text-success"><i class="bi bi-briefcase me-1"></i> {{ $booking->expenditureHead->code }} - {{ $booking->expenditureHead->name }}</div>
                            <small class="text-muted">Department: {{ $booking->expenditureHead->department ?? 'Central' }}</small>
                        @else
                            <div class="text-muted"><i class="bi bi-person-check me-1"></i> Self-Paid by Guest at Check-Out</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Allocated Rooms Block -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-door-open-fill text-success me-2"></i> Allocated Physical Rooms</h6>
                @if(auth()->user()->hasRole(['guest_house_incharge', 'guest_house_admin', 'system_admin']) && in_array($booking->status, ['Approved', 'Room Allocated']))
                    <a href="{{ route('allocations.form', $booking) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-repeat me-1"></i> Modify Room Allocation
                    </a>
                @endif
            </div>
            <div class="card-body p-4">
                @if($booking->bookingRooms->count() > 0)
                    <div class="row g-3">
                        @foreach($booking->bookingRooms as $br)
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold fs-5 text-dark"><i class="bi bi-key-fill text-warning me-2"></i> Room {{ $br->room->room_number }}</div>
                                        <small class="text-muted">{{ $br->room->floor ?? 'Main Floor' }} &bull; {{ $br->room->roomType->name }}</small>
                                        <div class="mt-1">
                                            <span class="badge bg-{{ $br->status === 'Occupied' ? 'success' : 'info text-dark' }}">
                                                Status: {{ $br->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-end text-muted small">
                                        Allocated by:<br><strong>{{ $br->allocatedBy->name }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle me-2"></i> No physical rooms allocated yet. The Incharge will assign rooms once the request has been approved.
                    </div>
                @endif
            </div>
        </div>

        <!-- Registered Guests Roster -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i> Guest Roster & Accompanying Persons</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Guest Name</th>
                            <th>Role / Relationship</th>
                            <th>Designation / Institute</th>
                            <th>Contact Phone</th>
                            <th>ID Card Type & Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->guests as $g)
                        <tr>
                            <td class="fw-bold">
                                {{ $g->name }}
                                @if($g->is_primary)
                                    <span class="badge bg-success ms-1">Primary Guest</span>
                                @endif
                            </td>
                            <td>{{ $g->relationship ?? 'Guest' }}</td>
                            <td>{{ $g->designation ?? 'N/A' }}</td>
                            <td>{{ $g->phone ?? 'N/A' }}</td>
                            <td>
                                @if($g->id_document_type)
                                    <span class="badge bg-light text-dark border">{{ $g->id_document_type }}: {{ $g->id_document_number }}</span>
                                @else
                                    <span class="text-muted small">To be checked at desk</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Status History Timeline -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Lifecycle & Approval Audit Trail</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush">
                    @forelse($booking->statusHistory as $hist)
                        <li class="list-group-item px-0 py-3 d-flex justify-content-between align-items-start border-bottom">
                            <div>
                                <div class="fw-bold text-dark">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2">{{ $hist->action }}</span>
                                    <span>{{ $hist->from_status ?? 'Initial' }} &rarr; {{ $hist->to_status }}</span>
                                </div>
                                @if($hist->remarks)
                                    <div class="bg-light p-2 rounded mt-2 small text-secondary">
                                        <strong>Remarks:</strong> {{ $hist->remarks }}
                                    </div>
                                @endif
                                <small class="text-muted mt-1 d-block">
                                    Action taken by: <strong>{{ $hist->actor->name }}</strong> ({{ $hist->workflow_stage ?? 'System' }})
                                </small>
                            </div>
                            <div class="text-end text-muted small" style="white-space: nowrap;">
                                {{ $hist->created_at->format('d M Y, h:i A') }}
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">No status history transitions recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Sidebar Column: Financial Summary & Documents -->
    <div class="col-lg-4">
        <!-- Financial Tariff Breakdown -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-coin text-success me-2"></i> Tariff & Billing Summary</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Guest Category:</span>
                    <span class="fw-semibold">{{ $booking->guestType->name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Applied Rate / Night:</span>
                    <span class="fw-semibold">₹{{ number_format($booking->applied_tariff_per_night, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Stay Duration:</span>
                    <span class="fw-semibold">{{ $booking->billable_nights }} night(s) &times; {{ $booking->rooms_requested }} room(s)</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small border-top pt-2">
                    <span class="text-muted">Room Charges:</span>
                    <span class="fw-bold">₹{{ number_format($booking->total_room_charges, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Taxes (GST):</span>
                    <span>₹{{ number_format($booking->total_tax_amount, 2) }}</span>
                </div>
                @if($booking->total_other_charges > 0)
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Other Incidental Charges:</span>
                    <span>₹{{ number_format($booking->total_other_charges, 2) }}</span>
                </div>
                @endif
                <hr class="my-2">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">Grand Total:</span>
                    <span class="fw-bold fs-5 text-success">₹{{ number_format($booking->grand_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Amount Paid:</span>
                    <span class="text-success fw-bold">₹{{ number_format($booking->paid_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Balance Due:</span>
                    <span class="fw-bold text-danger">₹{{ number_format($booking->balance_amount, 2) }}</span>
                </div>

                @if($booking->latestInvoice)
                    <a href="{{ route('billing.invoice', $booking->latestInvoice) }}" class="btn btn-outline-success w-100 btn-sm">
                        <i class="bi bi-receipt me-1"></i> View Official Invoice #{{ $booking->latestInvoice->invoice_number }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Supporting Documents Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-paperclip text-primary me-2"></i> Private Supporting Documents</h6>
            </div>
            <div class="card-body p-3">
                @if($booking->documents->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($booking->documents as $doc)
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small text-truncate" style="max-width: 180px;">{{ $doc->document_title }}</div>
                                    <small class="text-muted">{{ round($doc->file_size_bytes / 1024, 1) }} KB &bull; {{ $doc->mime_type }}</small>
                                </div>
                                <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">No documents uploaded with this requisition.</p>
                @endif
            </div>
        </div>

        <!-- Extension Requests Card -->
        @if($booking->extensions->count() > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-range text-warning me-2"></i> Stay Extensions</h6>
            </div>
            <div class="card-body p-3">
                @foreach($booking->extensions as $ext)
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-{{ $ext->status === 'Approved' ? 'success' : ($ext->status === 'Rejected' ? 'danger' : 'warning text-dark') }}">{{ $ext->status }}</span>
                            <small class="text-muted">+{{ $ext->additional_nights }} night(s)</small>
                        </div>
                        <div class="small mt-1">Requested Departure: <strong>{{ $ext->requested_departure_date->format('d M Y') }}</strong></div>
                        <div class="small text-muted">Reason: {{ $ext->reason }}</div>
                        @if($ext->status === 'Pending' && auth()->user()->hasRole(['approver', 'system_admin', 'guest_house_incharge']))
                            <form action="{{ route('operations.extension.decide', $ext) }}" method="POST" class="mt-2 d-flex gap-2">
                                @csrf
                                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Decision remarks..." required>
                                <button type="submit" name="decision" value="Approved" class="btn btn-sm btn-success">Approve</button>
                                <button type="submit" name="decision" value="Rejected" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ================= MODALS FOR ACTION WORKFLOWS ================= -->

<!-- Verify Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('workflow.action', $booking) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="verify">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i> Verify Booking Requisition</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are verifying booking <strong>{{ $booking->booking_reference }}</strong> for guest <strong>{{ $booking->primaryGuest?->name }}</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Verification Remarks / Observations</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Documents verified against departmental invitation orders. Eligible for Category A rate."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm & Forward for Approval</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('workflow.action', $booking) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="approve">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-award me-2"></i> Sanction / Approve Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Grant final institutional approval for reservation <strong>{{ $booking->booking_reference }}</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Approval Sanction Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Sanctioned under Dean Horticulture examination head. Incharge to allot VIP suite."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Sanction & Route to Incharge</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal (Mandatory Remarks) -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('workflow.action', $booking) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="reject">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i> Reject Booking Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger small">
                        <strong>Mandatory Policy Requirement:</strong> Rejections strictly require clear documented remarks explaining the reason.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mandatory Rejection Remarks <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for rejection (e.g. Ineligible category, invalid purpose, no budget)..." required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Return for Correction Modal (Mandatory Remarks) -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('workflow.action', $booking) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="return">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i> Return for Correction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        The applicant will be notified to revise their requisition and resubmit.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Required Corrections / Missing Details <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Please attach the signed duty invitation letter and correct the departure date..." required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Return to Applicant</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Check-in Modal -->
<div class="modal fade" id="checkInModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('operations.checkin', $booking) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-box-arrow-in-right me-2"></i> Record Guest Arrival & Check-in</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="identity_verified" id="identity_verified" value="1" required checked>
                        <label class="form-check-label fw-bold small" for="identity_verified">
                            Physical Photo Identity (Aadhaar / Passport / Govt ID) has been scrutinized and verified at the reception desk.
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Room Condition upon Handover</label>
                        <input type="text" name="room_condition" class="form-control" value="Room inspected, clean linen and toiletries supplied, keys handed over." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Check-In</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Check-out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('operations.checkout', $booking) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i> Record Guest Departure & Finalize Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Room Condition & Key Return <span class="text-danger">*</span></label>
                        <input type="text" name="room_condition" class="form-control" value="Room vacated in good condition, fixtures intact, keys returned." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Incidental / Dining Supplement Charge (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" name="additional_charges[0][amount]" class="form-control" placeholder="0.00">
                        </div>
                        <input type="text" name="additional_charges[0][description]" class="form-control mt-1" placeholder="Description (e.g. Dining service charge, extra bed)">
                        <input type="hidden" name="additional_charges[0][type]" value="meal">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Finalize Departure & Generate Bill</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stay Extension Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('operations.extension.request', $booking) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i> Request Stay Extension</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Current Departure Date</label>
                        <input type="text" class="form-control" value="{{ $booking->departure_date->format('d M Y') }}" disabled readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Requested Extended Departure Date <span class="text-danger">*</span></label>
                        <input type="date" name="requested_departure_date" class="form-control" min="{{ $booking->departure_date->copy()->addDay()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Extension <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Extension required for additional workshop deliberations..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Extension Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Self-Cancellation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-ban me-2"></i> Cancel Reservation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger fw-bold">Are you sure you want to cancel this reservation?</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="Meeting postponed / Flight cancelled..." required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

