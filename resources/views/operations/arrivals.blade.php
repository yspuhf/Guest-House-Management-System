@extends('layouts.app')

@section('title', 'Arrivals & Check-in Desk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-box-arrow-in-right text-success me-2"></i> Guest Arrivals & Check-in Desk</h4>
        <small class="text-muted">Perform photo identity verification, examine room keys, and record check-in arrival</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Scheduled Guest Arrivals</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Ref #</th>
                    <th>Guest Details</th>
                    <th>Guest House & Room(s)</th>
                    <th>Arrival Date & Time</th>
                    <th>Payment Policy Stage</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayArrivals as $b)
                <tr>
                    <td>
                        <a href="{{ route('bookings.show', $b) }}" class="fw-bold">{{ $b->booking_reference }}</a>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $b->primaryGuest?->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ $b->primaryGuest?->phone }} &bull; {{ $b->primaryGuest?->designation }}</small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $b->guestHouse->name }}</div>
                        @if($b->bookingRooms->count() > 0)
                            <span class="badge bg-success">
                                {{ $b->bookingRooms->map(fn($br) => 'Room ' . $br->room->room_number)->join(', ') }}
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">Rooms Not Allocated</span>
                        @endif
                    </td>
                    <td>
                        <div>{{ $b->arrival_date->format('d M Y') }}</div>
                        <small class="text-muted"><i class="bi bi-clock"></i> {{ $b->arrival_time }}</small>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ ucfirst($b->payment_stage_applied) }}
                        </span>
                        <div class="small fw-semibold mt-1 @if($b->payment_status === 'paid') text-success @else text-danger @endif">
                            {{ strtoupper($b->payment_status) }}
                        </div>
                    </td>
                    <td>
                        @include('partials.status_badge', ['status' => $b->status])
                    </td>
                    <td class="text-end">
                        <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-person-check me-1"></i> Open Check-in
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-door-closed fs-2 d-block text-secondary mb-2"></i> No upcoming arrivals pending check-in.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

