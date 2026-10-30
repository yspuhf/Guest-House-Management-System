@extends('layouts.app')

@section('title', 'Booking Register')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text text-primary me-2"></i> Guest House Reservation Register</h4>
        <small class="text-muted">Browse, track and inspect university accommodation requisitions</small>
    </div>
    <a href="{{ route('bookings.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-calendar-plus me-1"></i> New Reservation
    </a>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('bookings.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Search Reference</label>
                <input type="text" name="reference" class="form-control form-control-sm" placeholder="e.g. UGH-2026-0001" value="{{ request('reference') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Guest House</label>
                <select name="guest_house_id" class="form-select form-select-sm">
                    <option value="">-- All Properties --</option>
                    @foreach($guestHouses as $gh)
                        <option value="{{ $gh->id }}" {{ request('guest_house_id') == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All Statuses --</option>
                    @foreach([
                        'Draft', 'Submitted', 'Verification Pending', 'Approval Pending', 'Approved',
                        'Room Allocated', 'Checked In', 'Checked Out', 'Closed', 'Rejected',
                        'Returned for Correction', 'Cancelled'
                    ] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-filter"></i> Apply Filter
                </button>
                <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Register Table -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Ref #</th>
                    <th>Applicant / Dept</th>
                    <th>Primary Guest</th>
                    <th>Guest House & Room Type</th>
                    <th>Stay Dates (Nights)</th>
                    <th>Status</th>
                    <th>Financials</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td>
                        <a href="{{ route('bookings.show', $b) }}" class="fw-bold text-decoration-none">
                            {{ $b->booking_reference }}
                        </a>
                        <div class="text-muted" style="font-size: 0.72rem;">{{ $b->created_at->format('d M Y') }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $b->applicant->name }}</div>
                        <small class="text-muted">{{ $b->applicant->department ?? 'General' }}</small>
                    </td>
                    <td>
                        <div>{{ $b->primaryGuest?->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ $b->guestType->name }}</small>
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
                        <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-outline-primary" title="Open Dossier">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('bookings.print', $b) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Confirmation Voucher">
                            <i class="bi bi-printer"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i> No booking records matched your query filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection

