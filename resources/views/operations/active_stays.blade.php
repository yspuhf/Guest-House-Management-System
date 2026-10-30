@extends('layouts.app')

@section('title', 'Active In-House Stays')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i> Current In-House Guest Occupancy</h4>
        <small class="text-muted">Manage active stays, room service inspections, stay extensions, and check-out departure settlement</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Guests Currently in Residence (Checked In)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Ref #</th>
                    <th>Occupant Details</th>
                    <th>Property & Room</th>
                    <th>Check-in Timestamp</th>
                    <th>Scheduled Check-out</th>
                    <th>Running Bill</th>
                    <th class="text-end">Operations</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeStays as $b)
                <tr>
                    <td>
                        <a href="{{ route('bookings.show', $b) }}" class="fw-bold">{{ $b->booking_reference }}</a>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $b->primaryGuest?->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ $b->primaryGuest?->phone }} &bull; {{ $b->primaryGuest?->designation }}</small>
                    </td>
                    <td>
                        <div>{{ $b->guestHouse->name }}</div>
                        <span class="badge bg-primary">
                            {{ $b->bookingRooms->map(fn($br) => 'Room ' . $br->room->room_number)->join(', ') }}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">{{ $b->bookingRooms->first()?->checked_in_at?->format('d M, h:i A') ?? $b->arrival_date->format('d M Y') }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold text-danger">{{ $b->departure_date->format('d M Y') }}</div>
                        <small class="text-muted">Check-out by {{ $b->departure_time }}</small>
                    </td>
                    <td>
                        <div class="fw-bold">₹{{ number_format($b->grand_total, 2) }}</div>
                        <small class="badge @if($b->payment_status === 'paid') bg-success-subtle text-success @else bg-warning-subtle text-warning @endif">
                            {{ strtoupper($b->payment_status) }}
                        </small>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-eye"></i> Dossier
                        </a>
                        <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-box-arrow-right me-1"></i> Check-out
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-cup-hot fs-2 d-block text-secondary mb-2"></i> No active guests currently in residence.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

