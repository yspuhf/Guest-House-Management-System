@extends('layouts.app')

@section('title', 'User & Role Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-people text-primary me-2"></i> User Accounts & RBAC Roles</h4>
        <small class="text-muted">Manage university employee logins, assigned responsibilities, and role-based permissions</small>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="bi bi-person-plus me-1"></i> Create User Account
    </button>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('masters.users.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Filter by Institutional Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- All Roles --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Search Name, Email, or Employee ID</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search user..." value="{{ request('search') }}">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('masters.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Emp ID & Name</th>
                    <th>Email Address</th>
                    <th>Designation & Department</th>
                    <th>Primary Role</th>
                    <th>Contact Phone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div class="fw-bold fs-6">{{ $u->name }}</div>
                        <code>{{ $u->employee_id ?? 'N/A' }}</code>
                    </td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <div class="fw-semibold">{{ $u->designation ?? 'N/A' }}</div>
                        <small class="text-muted">{{ $u->department ?? 'General' }}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary text-uppercase" style="font-size: 0.72rem;">
                            {{ str_replace('_', ' ', $u->primary_role) }}
                        </span>
                    </td>
                    <td>{{ $u->phone ?? 'N/A' }}</td>
                    <td>
                        <span class="badge @if($u->is_active) bg-success @else bg-danger @endif">
                            {{ $u->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <form action="{{ route('masters.users.toggle_active', $u) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm @if($u->is_active) btn-outline-danger @else btn-outline-success @endif" title="Toggle Active / Deactivate">
                                <i class="bi bi-power"></i> {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No user accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal to Create User -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('masters.users.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Create User Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Ramesh Kumar" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">University Email ID <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="name@yspuniversity.ac.in" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" placeholder="e.g. EMP-109">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Contact Mobile</label>
                            <input type="tel" name="phone" class="form-control" placeholder="98160XXXXX">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Assistant Professor">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Department</label>
                            <input type="text" name="department" class="form-control" placeholder="e.g. Fruit Science">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Role <span class="text-danger">*</span></label>
                        <select name="primary_role" class="form-select" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->display_name }} ({{ $r->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" value="password123" required>
                        <div class="form-text">Default demo password: <code>password123</code></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

