@extends('layouts.app')

@section('title', 'Invoices & Billing Register')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-success me-2"></i> Invoices & Billing Register</h4>
        <small class="text-muted">Itemized university guest house billing records, receipts, and cashiering</small>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('billing.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control form-control-sm" placeholder="e.g. INV-2026-0001" value="{{ request('invoice_number') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Payment Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All Statuses --</option>
                    <option value="Unpaid" {{ request('status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Refunded" {{ request('status') == 'Refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-filter"></i> Filter Invoices
                </button>
                <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Booking Ref</th>
                    <th>Applicant / Guest</th>
                    <th>Property</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td>
                        <a href="{{ route('billing.invoice', $inv) }}" class="fw-bold">{{ $inv->invoice_number }}</a>
                    </td>
                    <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('bookings.show', $inv->booking) }}" class="text-decoration-none">
                            {{ $inv->booking->booking_reference }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $inv->booking->primaryGuest?->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ $inv->booking->applicant->name }}</small>
                    </td>
                    <td>{{ $inv->booking->guestHouse->name }}</td>
                    <td class="fw-bold">₹{{ number_format($inv->grand_total, 2) }}</td>
                    <td class="text-success fw-bold">₹{{ number_format($inv->paid_amount, 2) }}</td>
                    <td class="fw-bold @if($inv->balance_due > 0) text-danger @else text-muted @endif">
                        ₹{{ number_format($inv->balance_due, 2) }}
                    </td>
                    <td>
                        <span class="badge @if($inv->status === 'Paid') bg-success @elseif($inv->status === 'Unpaid') bg-danger @else bg-warning text-dark @endif">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('billing.invoice', $inv) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-receipt"></i> Inspect Bill
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i> No invoices found matching the criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

