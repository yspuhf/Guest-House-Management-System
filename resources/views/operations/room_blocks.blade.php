@extends('layouts.app')

@section('title', 'Maintenance & Hold Blocks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-slash-circle text-danger me-2"></i> Room Maintenance & Administrative Blocks</h4>
        <small class="text-muted">Temporarily block rooms for civil/electrical repairs, deep cleaning, or executive VIP holds</small>
    </div>
    <button type="button" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#newBlockModal">
        <i class="bi bi-plus-circle me-1"></i> Add Room Block
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Active & Scheduled Room Blocks</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Room & Property</th>
                    <th>Block Duration</th>
                    <th>Block Category</th>
                    <th>Reason / Justification</th>
                    <th>Blocked By</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blocks as $b)
                <tr>
                    <td>
                        <div class="fw-bold fs-6">Room {{ $b->room->room_number }}</div>
                        <small class="text-muted">{{ $b->room->guestHouse->name }}</small>
                    </td>
                    <td>
                        <div>{{ $b->start_date->format('d M Y') }} &rarr; {{ $b->end_date->format('d M Y') }}</div>
                        <small class="text-muted">{{ $b->start_date->diffInDays($b->end_date) }} Day(s) Interval</small>
                    </td>
                    <td>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-capitalize">
                            {{ str_replace('_', ' ', $b->block_type) }}
                        </span>
                    </td>
                    <td>{{ $b->reason }}</td>
                    <td>
                        <div class="fw-semibold">{{ $b->blockedBy->name }}</div>
                        <small class="text-muted">{{ $b->created_at->format('d M Y') }}</small>
                    </td>
                    <td>
                        @if($b->is_active)
                            <span class="badge bg-danger">Active Block</span>
                        @else
                            <span class="badge bg-secondary">Released</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($b->is_active)
                            <form action="{{ route('operations.blocks.remove', $b) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-unlock me-1"></i> Release Block
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-shield-check fs-2 d-block text-success mb-2"></i> No active maintenance or hold blocks. All published rooms are operational.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal to Create Block -->
<div class="modal fade" id="newBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('operations.blocks.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-slash-circle me-2"></i> Place Room Hold / Maintenance Block</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Room <span class="text-danger">*</span></label>
                        <select name="room_id" class="form-select" required>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">Room {{ $r->room_number }} - {{ $r->guestHouse->name }} ({{ $r->roomType->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Block Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Block End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Block Type <span class="text-danger">*</span></label>
                        <select name="block_type" class="form-select" required>
                            <option value="maintenance">Civil / Plumbing / Electrical Maintenance</option>
                            <option value="deep_cleaning">Sanitization & Deep Cleaning</option>
                            <option value="vip_hold">Executive VIP Hold (Chancellor / Minister / State Guest)</option>
                            <option value="administrative_block">Administrative Quarantine / Renovation</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason & Description <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Specify maintenance order number or VIP event name..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm & Place Block</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

