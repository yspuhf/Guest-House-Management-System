@extends('layouts.app')

@section('title', $guesthouse->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark">{{ $guesthouse->name }} (<code>{{ $guesthouse->code }}</code>)</h4>
        <small class="text-muted">{{ $guesthouse->address }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('masters.rooms.create') }}?guest_house_id={{ $guesthouse->id }}" class="btn btn-sm btn-success">
            <i class="bi bi-plus-circle me-1"></i> Add Room to Property
        </a>
        <a href="{{ route('masters.guesthouses.edit', $guesthouse) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Property
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 fw-bold text-dark">
                <i class="bi bi-info-circle text-primary me-2"></i> Configuration & Eligibility Rules
            </div>
            <div class="card-body">
                <p><strong>Description:</strong> {{ $guesthouse->description ?? 'No description provided.' }}</p>
                <div class="p-3 bg-light rounded border mb-3">
                    <strong class="d-block mb-1 text-secondary text-uppercase small">Eligibility Rules:</strong>
                    {{ $guesthouse->eligibility_rules ?? 'General institutional guidelines apply.' }}
                </div>
                <div class="small">
                    <div>Incharge: <strong>{{ $guesthouse->incharge?->name ?? 'Unassigned' }}</strong></div>
                    <div>Contact: {{ $guesthouse->contact_phone }} &bull; {{ $guesthouse->contact_email }}</div>
                    <div>Status: <span class="badge @if($guesthouse->is_active) bg-success @else bg-danger @endif">{{ $guesthouse->is_active ? 'Active' : 'Inactive' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 fw-bold text-dark">
                <i class="bi bi-door-closed text-success me-2"></i> Rooms in this Property ({{ $guesthouse->rooms->count() }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 250px;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Room #</th>
                                <th>Category</th>
                                <th>Floor</th>
                                <th>State</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guesthouse->rooms as $rm)
                            <tr>
                                <td class="fw-bold">Room {{ $rm->room_number }}</td>
                                <td>{{ $rm->roomType->name }}</td>
                                <td>{{ $rm->floor ?? 'Main' }}</td>
                                <td>
                                    <span class="badge @if($rm->maintenance_status === 'operational') bg-success @else bg-danger @endif" style="font-size: 0.7rem;">
                                        {{ $rm->maintenance_status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No rooms added yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

