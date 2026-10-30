@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-person-circle fs-4 text-success me-2"></i>
                <h5 class="mb-0">User Account Profile</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Official Email Address</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled readonly>
                            <div class="form-text">Managed by University Registrar IT Directory.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Employee ID</label>
                            <input type="text" class="form-control" value="{{ $user->employee_id ?? 'Not Assigned' }}" disabled readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Primary Institutional Role</label>
                            <input type="text" class="form-control text-capitalize" value="{{ str_replace('_', ' ', $user->primary_role) }}" disabled readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Designation</label>
                            <input type="text" name="designation" class="form-control" value="{{ old('designation', $user->designation) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Department / Division</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department', $user->department) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Mobile Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 fw-bold text-secondary">Change Password (Leave blank to keep existing)</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter new password">
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Profile Details
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

