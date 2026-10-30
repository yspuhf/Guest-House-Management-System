@extends('layouts.app')

@section('title', 'Edit ' . $guesthouse->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-pencil-square me-2"></i> Edit Guest House: {{ $guesthouse->name }}</h5>
                <a href="{{ route('masters.guesthouses.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('masters.guesthouses.update', $guesthouse) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Property Code</label>
                            <input type="text" class="form-control" value="{{ $guesthouse->code }}" disabled readonly>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Guest House Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $guesthouse->name) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Campus Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $guesthouse->address) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $guesthouse->description) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Eligibility Rules</label>
                            <textarea name="eligibility_rules" class="form-control" rows="2">{{ old('eligibility_rules', $guesthouse->eligibility_rules) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Assigned Incharge</label>
                            <select name="assigned_incharge_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($incharges as $inc)
                                    <option value="{{ $inc->id }}" {{ old('assigned_incharge_id', $guesthouse->assigned_incharge_id) == $inc->id ? 'selected' : '' }}>{{ $inc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Publication State</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ old('status', $guesthouse->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $guesthouse->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="deactivated" {{ old('status', $guesthouse->status) == 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Active Status</label>
                            <select name="is_active" class="form-select" required>
                                <option value="1" {{ old('is_active', $guesthouse->is_active) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !old('is_active', $guesthouse->is_active) ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $guesthouse->contact_person) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $guesthouse->contact_phone) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $guesthouse->contact_email) }}">
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Update Guest House
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

