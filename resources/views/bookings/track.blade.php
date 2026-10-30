@extends('layouts.auth')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9 col-lg-7">
        <div class="card shadow-lg border-0 p-4 p-md-5 bg-white rounded-3">
            <div class="text-center mb-4">
                <i class="bi bi-search text-success fs-1"></i>
                <h4 class="fw-bold mt-2 text-dark">Track Guest House Reservation</h4>
                <div class="text-muted small">Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni</div>
                <hr class="my-3">
            </div>

            <form action="{{ route('bookings.track') }}" method="GET" class="mb-4">
                <label class="form-label fw-semibold small">Enter Unique Booking Reference</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                    <input type="text" name="reference" class="form-control form-control-lg" placeholder="e.g. UGH-2026-0001" value="{{ request('reference') }}" required autofocus>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-search me-1"></i> Track Status
                    </button>
                </div>
            </form>

            @if(request()->filled('reference'))
                @if($booking)
                    <div class="border rounded p-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $booking->booking_reference }}</h5>
                                <small class="text-muted">Applied on {{ $booking->created_at->format('d M Y') }}</small>
                            </div>
                            <div>
                                @include('partials.status_badge', ['status' => $booking->status])
                            </div>
                        </div>

                        <div class="row g-3 small">
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Primary Guest:</span>
                                <strong class="fs-6">{{ $booking->primaryGuest?->name ?? 'Guest' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Guest House:</span>
                                <strong>{{ $booking->guestHouse->name }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Stay Period:</span>
                                <strong>{{ $booking->arrival_date->format('d M Y') }} &rarr; {{ $booking->departure_date->format('d M Y') }}</strong> ({{ $booking->billable_nights }} nights)
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Room Category:</span>
                                <strong>{{ $booking->roomType->name }} ({{ $booking->rooms_requested }} room)</strong>
                            </div>
                            @if($booking->bookingRooms->count() > 0)
                            <div class="col-12 bg-white p-2 rounded border">
                                <span class="text-muted d-block small">Allocated Physical Room(s):</span>
                                <span class="badge bg-success fs-6 me-1">
                                    <i class="bi bi-door-open-fill me-1"></i>
                                    {{ $booking->bookingRooms->map(fn($br) => 'Room ' . $br->room->room_number)->join(', ') }}
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Status History Flow -->
                        <h6 class="fw-bold text-dark mt-4 border-top pt-3 small text-uppercase">Tracking Progress & Audit Log:</h6>
                        <ul class="list-group list-group-flush small mt-2">
                            @foreach($booking->statusHistory as $hist)
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between">
                                    <div>
                                        <span class="badge bg-secondary me-2">{{ $hist->action }}</span>
                                        <strong>{{ $hist->to_status }}</strong>
                                        @if($hist->remarks)
                                            <div class="text-muted mt-1 fst-italic">"{{ $hist->remarks }}"</div>
                                        @endif
                                    </div>
                                    <div class="text-muted">{{ $hist->created_at->format('d M, h:i A') }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2"></i>
                        No reservation record found for reference <strong>"{{ request('reference') }}"</strong>. Please verify the code on your acknowledgement slip.
                    </div>
                @endif
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="btn btn-link text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> Back to University Portal Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

