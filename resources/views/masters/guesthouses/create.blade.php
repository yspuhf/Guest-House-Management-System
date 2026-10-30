@extends('layouts.app')

@section('title', 'Add New Guest House')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-building-add me-2"></i> Register New University Guest House</h5>
                <a href="{{ route('masters.guesthouses.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('masters.guesthouses.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Unique Property Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. FGH-02" value="{{ old('code') }}" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Guest House Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Faculty & International Guest House" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Campus Location / Physical Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="e.g. East Campus, Adjacent to Forestry Complex, Nauni Solan">{{ old('address') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description & Facilities Overview</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief summary of property amenities...">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Institutional Eligibility Rules & Priorities</label>
                            <textarea name="eligibility_rules" class="form-control" rows="2" placeholder="Who is eligible? Vice-Chancellor approval, ICAR delegates, etc.">{{ old('eligibility_rules') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Assigned Property Incharge</label>
                            <select name="assigned_incharge_id" class="form-select">
                                <option value="">-- Select Incharge --</option>
                                @foreach($incharges as $inc)
                                    <option value="{{ $inc->id }}" {{ old('assigned_incharge_id') == $inc->id ? 'selected' : '' }}>{{ $inc->name }} ({{ $inc->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Publication State</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft (Hidden from applicant booking form)</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published (Active for booking)</option>
                                <option value="deactivated" {{ old('status') == 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Mobile</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Official Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email') }}">
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Guest House
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

