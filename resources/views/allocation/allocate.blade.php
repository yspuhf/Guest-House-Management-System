@extends('layouts.app')

@section('title', 'Allocate Rooms for ' . $booking->booking_reference)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-door-open-fill me-2"></i> Physical Room Assignment</h5>
                    <small class="text-muted">Booking Reference: <strong>{{ $booking->booking_reference }}</strong></small>
                </div>
                <a href="{{ route('allocations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Allocation Desk
                </a>
            </div>

            <div class="card-body p-4">
                <!-- Stay Specs Alert -->
                <div class="bg-light p-3 rounded mb-4 border">
                    <div class="row g-2 small">
                        <div class="col-md-3"><strong>Guest House:</strong> {{ $booking->guestHouse->name }}</div>
                        <div class="col-md-3"><strong>Preferred Type:</strong> {{ $booking->roomType->name }}</div>
                        <div class="col-md-3"><strong>Arrival:</strong> {{ $booking->arrival_date->format('d M Y') }}</div>
                        <div class="col-md-3"><strong>Departure:</strong> {{ $booking->departure_date->format('d M Y') }}</div>
                        <div class="col-md-3"><strong>Rooms Needed:</strong> <span class="badge bg-primary fs-6">{{ $booking->rooms_requested }}</span></div>
                        <div class="col-md-3"><strong>Total Guests:</strong> {{ $booking->total_guests }}</div>
                        <div class="col-md-6"><strong>Primary Guest:</strong> {{ $booking->primaryGuest?->name ?? 'Guest' }} ({{ $booking->primaryGuest?->designation }})</div>
                    </div>
                </div>

                <form action="{{ route('allocations.store', $booking) }}" method="POST">
                    @csrf

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-check2-square text-success me-1"></i> Available Rooms for Stay Interval [{{ $booking->arrival_date->format('d M') }} &rarr; {{ $booking->departure_date->format('d M') }})
                    </h6>

                    @if($availableRooms->count() > 0)
                        <div class="row g-3 mb-4">
                            @foreach($availableRooms as $room)
                                @php
                                    $isSelected = in_array($room->id, old('room_ids', $allocatedRoomIds));
                                @endphp
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 @if($isSelected) border-primary bg-primary-subtle @else bg-white @endif">
                                        <div class="form-check">
                                            <input class="form-check-input room-checkbox" type="checkbox" name="room_ids[]" value="{{ $room->id }}" id="room_{{ $room->id }}" {{ $isSelected ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="room_{{ $room->id }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fs-5 fw-bold text-dark">Room {{ $room->room_number }}</span>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Available</span>
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    {{ $room->floor ?? 'Main Floor' }} &bull; {{ $room->roomType->name }}
                                                </div>
                                                <div class="small text-muted">
                                                    Max Occupancy: <strong>{{ $room->max_occupancy }} Guests</strong>
                                                </div>
                                                @if($room->facilities)
                                                    <div class="mt-1 d-flex flex-wrap gap-1">
                                                        @foreach($room->facilities as $fac)
                                                            <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">{{ $fac }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> No published operational rooms in <strong>{{ $booking->guestHouse->name }}</strong> match the requested category for this date range. You may need to inspect the availability calendar or place a temporary hold block.
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Allocation Operational Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. VIP floor room assigned with complimentary fruit basket on arrival.">
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <div class="small text-muted">
                            <i class="bi bi-shield-lock me-1"></i> Row-level database lock will prevent duplicate allocations.
                        </div>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold" @if($availableRooms->count() === 0) disabled @endif>
                            <i class="bi bi-check2-circle me-1"></i> Confirm Room Allocation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

