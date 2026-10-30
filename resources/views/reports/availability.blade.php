@extends('layouts.app')

@section('title', 'Room Availability & Occupancy Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-3x3 text-primary me-2"></i> Room Occupancy & Operational Status Report</h4>
        <small class="text-muted">Analyze room utility, active reservations, and maintenance downtime</small>
    </div>
    <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
        <i class="bi bi-printer me-1"></i> Print Summary
    </button>
</div>

<!-- Filter Bar -->
<div class="card shadow-sm border-0 mb-4 bg-white no-print">
    <div class="card-body p-3">
        <form action="{{ route('reports.availability') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Property</label>
                <select name="guest_house_id" class="form-select form-select-sm">
                    @foreach($guestHouses as $gh)
                        <option value="{{ $gh->id }}" {{ $selectedGhId == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Inspection Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Inspection End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-filter"></i> Run Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark small">
                <tr>
                    <th>Room #</th>
                    <th>Category</th>
                    <th>Floor</th>
                    <th>Maintenance State</th>
                    <th>Blocks During Period</th>
                    <th>Occupancies & Confirmed Bookings</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $r)
                <tr>
                    <td class="fw-bold fs-6">Room {{ $r->room_number }}</td>
                    <td>{{ $r->roomType->name }}</td>
                    <td>{{ $r->floor ?? 'Main Floor' }}</td>
                    <td>
                        <span class="badge @if($r->maintenance_status === 'operational') bg-success-subtle text-success border border-success-subtle @else bg-danger-subtle text-danger border border-danger-subtle @endif text-capitalize">
                            {{ str_replace('_', ' ', $r->maintenance_status) }}
                        </span>
                    </td>
                    <td>
                        @if($r->blocks->count() > 0)
                            @foreach($r->blocks as $blk)
                                <div class="badge bg-danger mb-1 d-block text-start">
                                    {{ ucfirst($blk->block_type) }}: {{ $blk->start_date->format('d M') }} &rarr; {{ $blk->end_date->format('d M') }} ({{ $blk->reason }})
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted small">No blocks</span>
                        @endif
                    </td>
                    <td>
                        @if($r->bookingRooms->count() > 0)
                            @foreach($r->bookingRooms as $br)
                                <div class="badge bg-primary mb-1 d-block text-start">
                                    {{ $br->booking->booking_reference }}: {{ $br->booking->primaryGuest?->name }} ({{ $br->booking->arrival_date->format('d M') }} to {{ $br->booking->departure_date->format('d M') }})
                                </div>
                            @endforeach
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i> Completely Vacant
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No rooms found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

