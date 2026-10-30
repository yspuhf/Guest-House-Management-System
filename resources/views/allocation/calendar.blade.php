@extends('layouts.app')

@section('title', 'Guest House Availability Calendar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar3 text-primary me-2"></i> Guest House Occupancy & Availability Calendar</h4>
        <small class="text-muted">Visual matrix of room availability and stay intervals [check-in, check-out)</small>
    </div>
</div>

<!-- Calendar Filters & Legend -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('allocations.calendar') }}" method="GET" class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Select Property</label>
                <select name="guest_house_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($guestHouses as $gh)
                        <option value="{{ $gh->id }}" {{ $selectedGhId == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Starting From</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}" onchange="this.form.submit()">
            </div>

            <div class="col-md-5 d-flex gap-2 justify-content-md-end">
                <a href="{{ route('allocations.calendar', ['guest_house_id' => $selectedGhId, 'start_date' => $startDate->copy()->subDays(7)->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i> Previous Week
                </a>
                <a href="{{ route('allocations.calendar', ['guest_house_id' => $selectedGhId, 'start_date' => date('Y-m-d')]) }}" class="btn btn-sm btn-outline-dark">
                    Today
                </a>
                <a href="{{ route('allocations.calendar', ['guest_house_id' => $selectedGhId, 'start_date' => $startDate->copy()->addDays(7)->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary">
                    Next Week <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </form>

        <!-- Official Textual Legend (Section 5) -->
        <div class="p-2 bg-light rounded d-flex flex-wrap gap-3 small border">
            <div class="fw-bold text-secondary">Legend:</div>
            <div><span class="legend-badge bg-success"></span> <strong>Available</strong> (Vacant & ready)</div>
            <div><span class="legend-badge bg-primary"></span> <strong>Booked/Occupied</strong> (Guest in residence)</div>
            <div><span class="legend-badge bg-warning"></span> <strong>Pending/Attention</strong> (Allocated / Incoming)</div>
            <div><span class="legend-badge bg-danger"></span> <strong>Unavailable/Maintenance</strong> (Repair / Cleaning / Hold)</div>
        </div>
    </div>
</div>

<!-- Calendar Grid Matrix -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.8rem;">
            <thead class="table-dark text-center">
                <tr>
                    <th style="min-width: 140px;" class="text-start">Room / Type</th>
                    @foreach($calendarData['dates'] as $date)
                        @php
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $isToday = $carbonDate->isToday();
                        @endphp
                        <th class="calendar-cell @if($isToday) bg-warning text-dark @endif">
                            <div class="fw-bold">{{ $carbonDate->format('D') }}</div>
                            <div style="font-size: 0.72rem;">{{ $carbonDate->format('d M') }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($calendarData['matrix'] as $row)
                <tr>
                    <td class="fw-bold text-start bg-light">
                        <div class="text-dark">Room {{ $row['room_number'] }}</div>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $row['room_type'] }}</small>
                    </td>

                    @foreach($calendarData['dates'] as $date)
                        @php
                            $cell = $row['status_daily'][$date];
                        @endphp
                        <td class="calendar-cell">
                            @if($cell['status'] === 'Available')
                                <span class="badge bg-success-subtle text-success w-100 py-2 border border-success-subtle" title="Available">
                                    <i class="bi bi-check"></i>
                                </span>
                            @elseif($cell['status'] === 'Booked/Occupied')
                                <span class="badge bg-primary w-100 py-2" title="{{ $cell['label'] }} ({{ $cell['guest'] }})">
                                    <i class="bi bi-person-fill"></i> Occupied
                                </span>
                            @elseif($cell['status'] === 'Pending/Attention')
                                <span class="badge bg-warning text-dark w-100 py-2" title="Allocated to {{ $cell['label'] }}">
                                    <i class="bi bi-clock"></i> Allocated
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle w-100 py-2" title="{{ $cell['label'] }}">
                                    <i class="bi bi-tools"></i> Maint.
                                </span>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($calendarData['dates']) + 1 }}" class="text-center py-5 text-muted">
                        No rooms configured in this guest house.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

