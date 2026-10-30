@extends('layouts.app')

@section('title', 'New Guest House Reservation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-success"><i class="bi bi-calendar2-plus me-2"></i> Guest House Reservation Requisition</h5>
                    <small class="text-muted">Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni</small>
                </div>
                <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Register
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('bookings.store') }}" method="POST" enctype="multipart/form-data" id="bookingForm">
                    @csrf

                    <!-- Section 1: Applicant & Sponsoring Details -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <span class="badge bg-success me-2">1</span> Applicant & Sponsoring Information
                        </h6>
                        <div class="row g-3 bg-light p-3 rounded">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Applicant Name</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Department / Division</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->department ?? 'General' }}" readonly disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Employee ID / Email</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->employee_id ?? 'EMP' }} ({{ auth()->user()->email }})" readonly disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Beneficiary Guest Details -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <span class="badge bg-success me-2">2</span> Primary Guest Details
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guest Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="primary_guest_name" class="form-control" value="{{ old('primary_guest_name') }}" placeholder="e.g. Prof. R.K. Sharma / Dr. Jane Doe" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Designation & Institution</label>
                                <input type="text" name="primary_guest_designation" class="form-control" value="{{ old('primary_guest_designation') }}" placeholder="e.g. Principal Scientist, ICAR-CPRI Shimla">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guest Contact Mobile <span class="text-danger">*</span></label>
                                <input type="tel" name="primary_guest_phone" class="form-control" value="{{ old('primary_guest_phone') }}" placeholder="10-digit mobile number" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guest Email Address</label>
                                <input type="email" name="primary_guest_email" class="form-control" value="{{ old('primary_guest_email') }}" placeholder="guest@institution.ac.in">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Identity Proof Type</label>
                                <select name="primary_guest_id_type" class="form-select">
                                    <option value="Aadhaar Card">Aadhaar Card</option>
                                    <option value="Govt Employee ID">Govt / University Employee ID</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Driving License">Driving License</option>
                                    <option value="Voter ID">Voter ID</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">ID Document / Card Number</label>
                                <input type="text" name="primary_guest_id_number" class="form-control" value="{{ old('primary_guest_id_number') }}" placeholder="ID card reference number">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Guest Category & Rate Classification <span class="text-danger">*</span></label>
                                <select name="guest_type_id" id="guest_type_id" class="form-select" required>
                                    @foreach($guestTypes as $gt)
                                        <option value="{{ $gt->id }}" {{ old('guest_type_id') == $gt->id ? 'selected' : '' }}>
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
                            <span class="badge bg-success me-2">3</span> Accommodation & Stay Requirements
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Target Guest House <span class="text-danger">*</span></label>
                                <select name="guest_house_id" id="guest_house_id" class="form-select" required>
                                    @foreach($guestHouses as $gh)
                                        <option value="{{ $gh->id }}" {{ old('guest_house_id') == $gh->id ? 'selected' : '' }}>
                                            {{ $gh->name }} ({{ $gh->address }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Preferred Room Category <span class="text-danger">*</span></label>
                                <select name="room_type_id" id="room_type_id" class="form-select" required>
                                    @foreach($roomTypes as $rt)
                                        <option value="{{ $rt->id }}" data-tariff="{{ $rt->default_tariff }}" {{ old('room_type_id') == $rt->id ? 'selected' : '' }}>
                                            {{ $rt->name }} (Standard Capacity: {{ $rt->standard_capacity }} Persons)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Arrival Date <span class="text-danger">*</span></label>
                                <input type="date" name="arrival_date" id="arrival_date" class="form-control" value="{{ old('arrival_date', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Arrival Time</label>
                                <input type="time" name="arrival_time" class="form-control" value="{{ old('arrival_time', '12:00') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Departure Date <span class="text-danger">*</span></label>
                                <input type="date" name="departure_date" id="departure_date" class="form-control" value="{{ old('departure_date', date('Y-m-d', strtotime('+3 days'))) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Departure Time</label>
                                <input type="time" name="departure_time" class="form-control" value="{{ old('departure_time', '11:00') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Number of Rooms <span class="text-danger">*</span></label>
                                <input type="number" name="rooms_requested" id="rooms_requested" class="form-control" min="1" max="10" value="{{ old('rooms_requested', 1) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Total Number of Guests <span class="text-danger">*</span></label>
                                <input type="number" name="total_guests" class="form-control" min="1" max="25" value="{{ old('total_guests', 1) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Dining / Meal Plan Preference</label>
                                <select name="meal_preference" class="form-select">
                                    <option value="None">No Meals Required (Accommodation Only)</option>
                                    <option value="Breakfast">Breakfast Included</option>
                                    <option value="Breakfast & Dinner">Breakfast & Dinner</option>
                                    <option value="All Meals (Breakfast, Lunch, Dinner)">Full Board (Breakfast, Lunch & Dinner)</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Official Purpose of Visit <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="2" placeholder="Specify official event, viva examiner duty, conference, research visit or personal grounds..." required>{{ old('purpose') }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Special Needs / Requests (Optional)</label>
                                <input type="text" name="special_requests" class="form-control" value="{{ old('special_requests') }}" placeholder="e.g. Ground floor preference for elderly, late check-in, dietary restrictions">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Budget Head & Sanction Document -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <span class="badge bg-success me-2">4</span> Expenditure Head & Supporting Sanction Letter
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Head of Account / Expenditure Head</label>
                                <select name="expenditure_head_id" class="form-select">
                                    <option value="">-- Direct Self-Payment by Guest --</option>
                                    @foreach($expenditureHeads as $eh)
                                        <option value="{{ $eh->id }}" {{ old('expenditure_head_id') == $eh->id ? 'selected' : '' }}>
                                            {{ $eh->code }} - {{ $eh->name }} ({{ $eh->department ?? 'Central' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Choose university budget head if stay is sponsored by project/department.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Upload Sanction / Invitation / Duty Order</label>
                                <input type="file" name="approval_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Max 5MB. Private & confidential upload.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Acceptance -->
                    <div class="p-3 bg-light rounded mb-4 border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="accepted_terms" id="accepted_terms" value="1" checked required>
                            <label class="form-check-label small" for="accepted_terms">
                                I hereby declare that the details provided are authentic and compliant with Dr. Y.S. Parmar UHF Guest House Rules. I agree that reservations are subject to institutional approval and room availability. Departure check-out is standard at 11:00 AM.
                            </label>
                        </div>
                    </div>

                    <!-- Form Submission Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-save me-1"></i> Save as Draft
                        </button>
                        <button type="submit" name="action_type" value="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                            <i class="bi bi-send-check-fill me-1"></i> Submit Request for Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

