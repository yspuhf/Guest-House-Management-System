@extends('layouts.app')

@section('title', 'Room Allocation Desk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-door-open-fill text-primary me-2"></i> Physical Room Allocation Desk</h4>
        <small class="text-muted">Assign physical guest house rooms to approved reservations</small>
    </div>
    <a href="{{ route('allocations.calendar') }}" class="btn btn-outline-primary shadow-sm">
        <i class="bi bi-calendar3 me-1"></i> Live Availability Calendar
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Approved Reservations Eligible for Room Assignment</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Ref #</th>
                    <th>Applicant / Dept</th>
                    <th>Primary Guest</th>
                    <th>Property & Preferred Category</th>
                    <th>Stay Dates (Nights)</th>
                    <th>Rooms Needed</th>
                    <th>Current Allocation</th>
                    <th class="text-end">Allocation Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedBookings as $b)
                <tr>
                    <td>
                        <a href="{{ route('bookings.show', $b) }}" class="fw-bold">{{ $b->booking_reference }}</a>
                        <div class="text-muted small">{{ $b->created_at->format('d M Y') }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $b->applicant->name }}</div>
                        <small class="text-muted">{{ $b->applicant->department }}</small>
                    </td>
                    <td>
                        <div>{{ $b->primaryGuest?->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ $b->guestType->name }}</small>
                    </td>
                    <td>
                        <div>{{ $b->guestHouse->name }}</div>
                        <small class="text-muted">{{ $b->roomType->name }}</small>
                    </td>
                    <td>
                        <div>{{ $b->arrival_date->format('d M') }} &rarr; {{ $b->departure_date->format('d M Y') }}</div>
                        <small class="badge bg-light text-dark border">{{ $b->billable_nights }} Night(s)</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary fs-6">{{ $b->rooms_requested }} Room(s)</span>
                    </td>
                    <td>
                        @if($b->bookingRooms->count() > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                                <i class="bi bi-door-open me-1"></i> {{ $b->bookingRooms->map(fn($br) => $br->room->room_number)->join(', ') }}
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                <i class="bi bi-exclamation-circle me-1"></i> Not Assigned
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('allocations.form', $b) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-key-fill me-1"></i> {{ $b->bookingRooms->count() > 0 ? 'Change Rooms' : 'Allocate Rooms' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-check2-circle fs-2 d-block text-success mb-2"></i> All approved bookings have been allocated rooms.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

