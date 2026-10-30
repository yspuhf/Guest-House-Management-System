@extends('layouts.app')

@section('title', 'Rooms Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-door-closed text-primary me-2"></i> Physical Rooms Master</h4>
        <small class="text-muted">Manage specific rooms, floors, amenities, publication, and maintenance operational state</small>
    </div>
    <a href="{{ route('masters.rooms.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Physical Room
    </a>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('masters.rooms.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Filter by Property</label>
                <select name="guest_house_id" class="form-select form-select-sm">
                    <option value="">-- All Properties --</option>
                    @foreach($guestHouses as $gh)
                        <option value="{{ $gh->id }}" {{ request('guest_house_id') == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Category</label>
                <select name="room_type_id" class="form-select form-select-sm">
                    <option value="">-- All Categories --</option>
                    @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}" {{ request('room_type_id') == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Operational State</label>
                <select name="maintenance_status" class="form-select form-select-sm">
                    <option value="">-- All States --</option>
                    <option value="operational" {{ request('maintenance_status') == 'operational' ? 'selected' : '' }}>Operational</option>
                    <option value="under_maintenance" {{ request('maintenance_status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    <option value="cleaning_in_progress" {{ request('maintenance_status') == 'cleaning_in_progress' ? 'selected' : '' }}>Cleaning in Progress</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Room #</th>
                    <th>Guest House Property</th>
                    <th>Category & Floor</th>
                    <th>Max Occupancy</th>
                    <th>Override Tariff</th>
                    <th>Publication</th>
                    <th>Maintenance Operational State</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $rm)
                <tr>
                    <td>
                        <span class="fs-5 fw-bold text-dark">Room {{ $rm->room_number }}</span>
                    </td>
                    <td>{{ $rm->guestHouse->name }}</td>
                    <td>
                        <div class="fw-semibold">{{ $rm->roomType->name }}</div>
                        <small class="text-muted">{{ $rm->floor ?? 'Main Floor' }}</small>
                    </td>
                    <td><span class="badge bg-secondary">{{ $rm->max_occupancy }} Persons</span></td>
                    <td>
                        @if($rm->tariff)
                            <strong class="text-success">₹{{ number_format($rm->tariff, 2) }}</strong>
                        @else
                            <span class="text-muted small">Type Default</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge @if($rm->publication_status === 'published') bg-primary @else bg-secondary @endif text-capitalize">
                            {{ $rm->publication_status }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown d-inline">
                            <button class="btn btn-sm @if($rm->maintenance_status === 'operational') btn-outline-success @else btn-outline-danger @endif dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                {{ ucfirst(str_replace('_', ' ', $rm->maintenance_status)) }}
                            </button>
                            <ul class="dropdown-menu shadow">
                                <li>
                                    <form action="{{ route('masters.rooms.toggle_maintenance', $rm) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="operational">
                                        <button class="dropdown-item text-success" type="submit"><i class="bi bi-check-circle me-1"></i> Operational</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('masters.rooms.toggle_maintenance', $rm) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="under_maintenance">
                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-tools me-1"></i> Under Maintenance</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('masters.rooms.toggle_maintenance', $rm) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="cleaning_in_progress">
                                        <button class="dropdown-item text-warning" type="submit"><i class="bi bi-droplet me-1"></i> Cleaning in Progress</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('masters.rooms.edit', $rm) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">No rooms found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rooms->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $rooms->links() }}
        </div>
    @endif
</div>
@endsection

