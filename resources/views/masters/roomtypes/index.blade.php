@extends('layouts.app')

@section('title', 'Room Type Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tags text-primary me-2"></i> Room Type Master</h4>
        <small class="text-muted">Standard room classifications, default tariffs, and base occupancy capacities</small>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newRoomTypeModal">
        <i class="bi bi-plus-circle me-1"></i> Add Room Type
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Code & Category Name</th>
                    <th>Description</th>
                    <th>Standard Capacity</th>
                    <th>Default Base Tariff (INR)</th>
                    <th>Configured Rooms</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roomTypes as $rt)
                <tr>
                    <td>
                        <div class="fw-bold fs-6">{{ $rt->name }}</div>
                        <code>{{ $rt->code }}</code>
                    </td>
                    <td>{{ $rt->description ?? 'N/A' }}</td>
                    <td><span class="badge bg-secondary">{{ $rt->standard_capacity }} Persons</span></td>
                    <td class="fw-bold text-success fs-6">₹{{ number_format($rt->default_tariff, 2) }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $rt->rooms_count }} Rooms</span></td>
                    <td>
                        <span class="badge @if($rt->is_active) bg-success @else bg-danger @endif">
                            {{ $rt->is_active ? 'Active' : 'Deactivated' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal_{{ $rt->id }}">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <form action="{{ route('masters.roomtypes.destroy', $rt) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate or delete this room type?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal_{{ $rt->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('masters.roomtypes.update', $rt) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Edit Room Type: {{ $rt->name }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $rt->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Default Base Tariff (INR) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="default_tariff" class="form-control" value="{{ $rt->default_tariff }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Standard Capacity (Persons) <span class="text-danger">*</span></label>
                                        <input type="number" name="standard_capacity" class="form-control" value="{{ $rt->standard_capacity }}" min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ $rt->description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Active Status</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" {{ $rt->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$rt->is_active ? 'selected' : '' }}>Deactivated</option>
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
                    <td colspan="7" class="text-center py-5 text-muted">No room types configured.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- New Room Type Modal -->
<div class="modal fade" id="newRoomTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('masters.roomtypes.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-tag me-2"></i> Add Room Type</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Type Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. VIP-SUITE" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. VIP Presidential Suite" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Default Base Tariff (INR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="default_tariff" class="form-control" placeholder="2500.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Standard Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="standard_capacity" class="form-control" value="2" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Suite features, living lounge..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Room Type</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

