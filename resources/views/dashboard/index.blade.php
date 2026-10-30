@extends('layouts.app')

@section('title', 'Institutional Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Welcome, {{ auth()->user()->name }}</h3>
        <p class="text-muted mb-0 small">
            <span class="badge bg-success-subtle text-success border border-success-subtle me-2">
                <i class="bi bi-person-badge"></i> {{ ucwords(str_replace('_', ' ', auth()->user()->primary_role)) }}
            </span>
            <span>Department: <strong>{{ auth()->user()->department ?? 'General Administration' }}</strong></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bookings.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-calendar-plus me-1"></i> New Reservation
        </a>
        <a href="{{ route('allocations.calendar') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-calendar3 me-1"></i> Live Calendar
        </a>
    </div>
</div>

<!-- Stats Counter Grid -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Bookings</div>
                        <h3 class="fw-bold my-1 text-dark">{{ $stats['total_bookings'] }}</h3>
                        <small class="text-muted">Lifecycle requests</small>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Pending Scrutiny</div>
                        <h3 class="fw-bold my-1 text-warning">{{ $stats['pending_verifications'] + $stats['pending_approvals'] }}</h3>
                        <small class="text-muted">{{ $stats['pending_verifications'] }} Verif &bull; {{ $stats['pending_approvals'] }} Appr</small>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Ready for Allocation</div>
                        <h3 class="fw-bold my-1 text-info">{{ $stats['pending_allocations'] }}</h3>
                        <small class="text-muted">Approved reservations</small>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="bi bi-door-open fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Active Occupancy</div>
                        <h3 class="fw-bold my-1 text-success">{{ $stats['active_checked_in'] }}</h3>
                        <small class="text-muted">Guests in-house today</small>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Workflow Alert Panels if Actions Pending -->
@if(count($pendingApprovals) > 0)
<div class="card border-warning mb-4 shadow-sm">
    <div class="card-header bg-warning-subtle text-dark fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> High Priority: Requests Awaiting Your Sanction ({{ count($pendingApprovals) }})</span>
        <a href="{{ route('workflow.pending') }}" class="btn btn-sm btn-warning">Open Approval Queue</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Ref #</th>
                    <th>Applicant</th>
                    <th>Primary Guest</th>
                    <th>Guest House</th>
                    <th>Stay Dates</th>
                    <th>Purpose</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingApprovals as $bkg)
                <tr>
                    <td class="fw-bold"><a href="{{ route('bookings.show', $bkg) }}">{{ $bkg->booking_reference }}</a></td>
                    <td>{{ $bkg->applicant->name }} <br><small class="text-muted">{{ $bkg->applicant->department }}</small></td>
                    <td>{{ $bkg->primaryGuest?->name ?? 'Guest' }}</td>
                    <td>{{ $bkg->guestHouse->name }}</td>
                    <td>{{ $bkg->arrival_date->format('d M') }} &rarr; {{ $bkg->departure_date->format('d M Y') }}</td>
                    <td class="small text-truncate" style="max-width: 200px;">{{ $bkg->purpose }}</td>
                    <td>
                        <a href="{{ route('bookings.show', $bkg) }}" class="btn btn-sm btn-outline-primary">
                            Review & Sanction
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Bookings Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Recent Booking Activity</h5>
        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">View Full Register</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Reference</th>
                    <th>Applicant / Department</th>
                    <th>Guest Name</th>
                    <th>Guest House & Room Type</th>
                    <th>Dates (Nights)</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $b)
                <tr>
                    <td>
                        <a href="{{ route('bookings.show', $b) }}" class="fw-bold text-decoration-none">
                            {{ $b->booking_reference }}
                        </a>
                        <div class="text-muted" style="font-size: 0.72rem;">{{ $b->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $b->applicant->name }}</div>
                        <small class="text-muted">{{ $b->applicant->department ?? 'General' }}</small>
                    </td>
                    <td>
                        {{ $b->primaryGuest?->name ?? 'Primary Guest' }}
                        @if($b->total_guests > 1)
                            <span class="badge bg-light text-dark border">+{{ $b->total_guests - 1 }}</span>
                        @endif
                    </td>
                    <td>
                        <div>{{ $b->guestHouse->name }}</div>
                        <small class="text-muted">{{ $b->roomType->name }} ({{ $b->rooms_requested }} room)</small>
                    </td>
                    <td>
                        <div>{{ $b->arrival_date->format('d M') }} &rarr; {{ $b->departure_date->format('d M Y') }}</div>
                        <small class="badge bg-light text-dark border">{{ $b->billable_nights }} Night(s)</small>
                    </td>
                    <td>
                        @include('partials.status_badge', ['status' => $b->status])
                    </td>
                    <td>
                        <div class="fw-bold">₹{{ number_format($b->grand_total, 2) }}</div>
                        <small class="badge @if($b->payment_status === 'paid') bg-success-subtle text-success @else bg-danger-subtle text-danger @endif">
                            {{ strtoupper($b->payment_status) }}
                        </small>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-outline-secondary" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('bookings.print', $b) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Voucher">
                            <i class="bi bi-printer"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i> No booking requests found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

