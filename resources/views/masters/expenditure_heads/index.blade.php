@extends('layouts.app')

@section('title', 'Expenditure Head Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase text-primary me-2"></i> Expenditure Heads & Account Master</h4>
        <small class="text-muted">Manage university budget heads, research projects, and departmental funds</small>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newHeadModal">
        <i class="bi bi-plus-circle me-1"></i> Add Expenditure Head
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Budget Code</th>
                    <th>Scheme / Account Head Name</th>
                    <th>Managing Department</th>
                    <th>Requires Approval Letter</th>
                    <th>Associated Bookings</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenditureHeads as $eh)
                <tr>
                    <td><code>{{ $eh->code }}</code></td>
                    <td class="fw-bold text-dark">{{ $eh->name }}</td>
                    <td>{{ $eh->department ?? 'Central University' }}</td>
                    <td>
                        @if($eh->requires_approval_document)
                            <span class="badge bg-warning text-dark"><i class="bi bi-paperclip me-1"></i> Mandatory Document</span>
                        @else
                            <span class="badge bg-light text-muted border">Optional</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $eh->bookings_count }} Bookings</span></td>
                    <td>
                        <span class="badge @if($eh->is_active) bg-success @else bg-danger @endif">
                            {{ $eh->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editHead_{{ $eh->id }}">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editHead_{{ $eh->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('masters.expenditure-heads.update', $eh) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Edit Head: {{ $eh->code }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Head Title <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $eh->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Department</label>
                                        <input type="text" name="department" class="form-control" value="{{ $eh->department }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Requires Sanction Letter</label>
                                        <select name="requires_approval_document" class="form-select">
                                            <option value="1" {{ $eh->requires_approval_document ? 'selected' : '' }}>Yes (Mandatory upload)</option>
                                            <option value="0" {{ !$eh->requires_approval_document ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" {{ $eh->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$eh->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No expenditure heads registered.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- New Head Modal -->
<div class="modal fade" id="newHeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('masters.expenditure-heads.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-briefcase me-2"></i> Add Expenditure Head</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Budget Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. ICAR-PROJECT-2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Head Title / Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. AICRP Agroforestry Scheme" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Controlling Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g. Directorate of Research">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Requires Sanction Attachment</label>
                        <select name="requires_approval_document" class="form-select">
                            <option value="1" selected>Yes (Mandatory)</option>
                            <option value="0">No (Optional)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Budget Head</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

