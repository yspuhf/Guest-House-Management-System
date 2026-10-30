@extends('layouts.app')

@section('title', 'Edit Booking Requisition')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-warning"><i class="bi bi-pencil-square me-2"></i> Edit Requisition: {{ $booking->booking_reference }}</h5>
                    <small class="text-muted">Status: <strong>{{ $booking->status }}</strong></small>
                </div>
                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Booking
                </a>
            </div>

            <div class="card-body p-4">
                @if($booking->isReturned())
                    <div class="alert alert-warning mb-4">
                        <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Request Returned for Correction:</h6>
                        <p class="mb-0">{{ $booking->statusHistory->first()?->remarks ?? 'Please review the corrections requested by the scrutinizer.' }}</p>
                    </div>
                @endif

                <form action="{{ route('bookings.update', $booking) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Section 2: Beneficiary Guest Details -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <span class="badge bg-warning text-dark me-2">1</span> Primary Guest Details
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guest Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="primary_guest_name" class="form-control" value="{{ old('primary_guest_name', $booking->primaryGuest?->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Designation & Institution</label>
                                <input type="text" name="primary_guest_designation" class="form-control" value="{{ old('primary_guest_designation', $booking->primaryGuest?->designation) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guest Contact Mobile <span class="text-danger">*</span></label>
                                <input type="tel" name="primary_guest_phone" class="form-control" value="{{ old('primary_guest_phone', $booking->primaryGuest?->phone) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guest Email Address</label>
                                <input type="email" name="primary_guest_email" class="form-control" value="{{ old('primary_guest_email', $booking->primaryGuest?->email) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Identity Proof Type</label>
                                <select name="primary_guest_id_type" class="form-select">
                                    <option value="Aadhaar Card" {{ $booking->primaryGuest?->id_document_type == 'Aadhaar Card' ? 'selected' : '' }}>Aadhaar Card</option>
                                    <option value="Govt Employee ID" {{ $booking->primaryGuest?->id_document_type == 'Govt Employee ID' ? 'selected' : '' }}>Govt / University Employee ID</option>
                                    <option value="Passport" {{ $booking->primaryGuest?->id_document_type == 'Passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="Driving License" {{ $booking->primaryGuest?->id_document_type == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">ID Document / Card Number</label>
                                <input type="text" name="primary_guest_id_number" class="form-control" value="{{ old('primary_guest_id_number', $booking->primaryGuest?->id_document_number) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Guest Category & Rate Classification <span class="text-danger">*</span></label>
                                <select name="guest_type_id" class="form-select" required>
                                    @foreach($guestTypes as $gt)
                                        <option value="{{ $gt->id }}" {{ old('guest_type_id', $booking->guest_type_id) == $gt->id ? 'selected' : '' }}>
                                            {{ $gt->name }} - {{ $gt->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Accommodation & Stay Details -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <span class="badge bg-warning text-dark me-2">2</span> Accommodation & Stay Requirements
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Target Guest House <span class="text-danger">*</span></label>
                                <select name="guest_house_id" class="form-select" required>
                                    @foreach($guestHouses as $gh)
                                        <option value="{{ $gh->id }}" {{ old('guest_house_id', $booking->guest_house_id) == $gh->id ? 'selected' : '' }}>
                                            {{ $gh->name }} ({{ $gh->address }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Preferred Room Category <span class="text-danger">*</span></label>
                                <select name="room_type_id" class="form-select" required>
                                    @foreach($roomTypes as $rt)
                                        <option value="{{ $rt->id }}" {{ old('room_type_id', $booking->room_type_id) == $rt->id ? 'selected' : '' }}>
                                            {{ $rt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Arrival Date <span class="text-danger">*</span></label>
                                <input type="date" name="arrival_date" class="form-control" value="{{ old('arrival_date', $booking->arrival_date->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Arrival Time</label>
                                <input type="time" name="arrival_time" class="form-control" value="{{ old('arrival_time', $booking->arrival_time) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Departure Date <span class="text-danger">*</span></label>
                                <input type="date" name="departure_date" class="form-control" value="{{ old('departure_date', $booking->departure_date->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Departure Time</label>
                                <input type="time" name="departure_time" class="form-control" value="{{ old('departure_time', $booking->departure_time) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Number of Rooms <span class="text-danger">*</span></label>
                                <input type="number" name="rooms_requested" class="form-control" min="1" max="10" value="{{ old('rooms_requested', $booking->rooms_requested) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Total Number of Guests <span class="text-danger">*</span></label>
                                <input type="number" name="total_guests" class="form-control" min="1" max="25" value="{{ old('total_guests', $booking->total_guests) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Dining / Meal Plan</label>
                                <select name="meal_preference" class="form-select">
                                    <option value="None" {{ $booking->meal_preference == 'None' ? 'selected' : '' }}>No Meals</option>
                                    <option value="Breakfast" {{ $booking->meal_preference == 'Breakfast' ? 'selected' : '' }}>Breakfast Included</option>
                                    <option value="Breakfast & Dinner" {{ $booking->meal_preference == 'Breakfast & Dinner' ? 'selected' : '' }}>Breakfast & Dinner</option>
                                    <option value="All Meals (Breakfast, Lunch, Dinner)" {{ $booking->meal_preference == 'All Meals (Breakfast, Lunch, Dinner)' ? 'selected' : '' }}>Full Board</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Official Purpose of Visit <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="2" required>{{ old('purpose', $booking->purpose) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden terms -->
                    <input type="hidden" name="accepted_terms" value="1">

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" name="action_type" value="save" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-save me-1"></i> Update Draft
                        </button>
                        <button type="submit" name="action_type" value="submit" class="btn btn-warning px-5 py-2 fw-semibold">
                            <i class="bi bi-send-check-fill me-1"></i> Submit for Scrutiny / Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

