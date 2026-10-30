@extends('layouts.app')

@section('title', 'Guest House Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-houses text-primary me-2"></i> University Guest Houses Master</h4>
        <small class="text-muted">Configure campus properties, eligibility rules, assigned incharges, and publication status</small>
    </div>
    <a href="{{ route('masters.guesthouses.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Guest House
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Code & Name</th>
                    <th>Location / Address</th>
                    <th>Assigned Incharge</th>
                    <th>Total Rooms</th>
                    <th>Status</th>
                    <th>Publication</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guestHouses as $gh)
                <tr>
                    <td>
                        <div class="fw-bold fs-6">{{ $gh->name }}</div>
                        <code>{{ $gh->code }}</code>
                    </td>
                    <td>{{ $gh->address ?? 'Main Campus, Nauni' }}</td>
                    <td>
                        <div class="fw-semibold">{{ $gh->incharge?->name ?? 'Unassigned' }}</div>
                        <small class="text-muted">{{ $gh->contact_phone }}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary fs-6">{{ $gh->rooms->count() }} Rooms</span>
                    </td>
                    <td>
                        <span class="badge @if($gh->is_active) bg-success @else bg-danger @endif">
                            {{ $gh->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge @if($gh->status === 'published') bg-primary @elseif($gh->status === 'draft') bg-warning text-dark @else bg-secondary @endif text-capitalize">
                            {{ $gh->status }}
                        </span>
                    </td>
                    <td class="text-end">
                        <form action="{{ route('masters.guesthouses.toggle_publish', $gh) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark me-1" title="Toggle Draft / Published">
                                <i class="bi bi-arrow-repeat"></i> {{ $gh->status === 'published' ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                        <a href="{{ route('masters.guesthouses.show', $gh) }}" class="btn btn-sm btn-outline-info me-1" title="View Property">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('masters.guesthouses.edit', $gh) }}" class="btn btn-sm btn-outline-primary" title="Edit Property">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No guest houses registered.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

