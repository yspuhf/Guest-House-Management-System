@extends('layouts.app')

@section('title', 'Bookings Report & Export')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i> Comprehensive Bookings Report</h4>
        <small class="text-muted">Generate official audit listings, filter by date boundaries, and export to CSV</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-filetype-csv me-1"></i> Export to CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4 bg-white no-print">
    <div class="card-body p-3">
        <form action="{{ route('reports.bookings') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Guest House</label>
                <select name="guest_house_id" class="form-select form-select-sm">
                    <option value="">-- All Guest Houses --</option>
                    @foreach($guestHouses as $gh)
                        <option value="{{ $gh->id }}" {{ request('guest_house_id') == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Guest Category</label>
                <select name="guest_type_id" class="form-select form-select-sm">
                    <option value="">-- All Categories --</option>
                    @foreach($guestTypes as $gt)
                        <option value="{{ $gt->id }}" {{ request('guest_type_id') == $gt->id ? 'selected' : '' }}>{{ $gt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Arrival From</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Arrival To</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Generate Filtered Report
                </button>
                <a href="{{ route('reports.bookings') }}" class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Table -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="table-dark">
                <tr>
                    <th>Ref #</th>
                    <th>Applicant Name & Dept</th>
                    <th>Primary Guest</th>
                    <th>Guest House</th>
                    <th>Stay Interval</th>
                    <th>Nights</th>
                    <th>Status</th>
                    <th>Rate/Night</th>
                    <th>Total Billed</th>
                    <th>Paid</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td class="fw-bold">{{ $b->booking_reference }}</td>
                    <td>
                        <strong>{{ $b->applicant->name }}</strong><br>
                        <span class="text-muted">{{ $b->applicant->department }}</span>
                    </td>
                    <td>{{ $b->primaryGuest?->name ?? 'Guest' }}</td>
                    <td>{{ $b->guestHouse->name }}</td>
                    <td>{{ $b->arrival_date->format('d/m/Y') }} &rarr; {{ $b->departure_date->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $b->billable_nights }}</td>
                    <td><span class="badge bg-secondary">{{ $b->status }}</span></td>
                    <td>₹{{ number_format($b->applied_tariff_per_night, 2) }}</td>
                    <td class="fw-bold">₹{{ number_format($b->grand_total, 2) }}</td>
                    <td class="text-success">₹{{ number_format($b->paid_amount, 2) }}</td>
                    <td class="@if($b->balance_amount > 0) text-danger fw-bold @else text-muted @endif">₹{{ number_format($b->balance_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        No bookings matched the selected report filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
        <div class="card-footer bg-white py-3 no-print">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection

