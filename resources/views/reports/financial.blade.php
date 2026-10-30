@extends('layouts.app')

@section('title', 'Financial & Revenue Summary')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack text-success me-2"></i> Financial & Revenue Audit Report</h4>
        <small class="text-muted">Total billing, cashier collections, and outstanding receivable register</small>
    </div>
    <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
        <i class="bi bi-printer me-1"></i> Print Financial Summary
    </button>
</div>

<!-- Aggregates KPI Grid -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4 bg-white p-3">
            <div class="text-muted small fw-semibold text-uppercase">Total Amount Billed</div>
            <h3 class="fw-bold my-1 text-primary">₹{{ number_format($totalBilled, 2) }}</h3>
            <small class="text-muted">Aggregated across all generated invoices</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4 bg-white p-3">
            <div class="text-muted small fw-semibold text-uppercase">Total Collections Received</div>
            <h3 class="fw-bold my-1 text-success">₹{{ number_format($totalCollected, 2) }}</h3>
            <small class="text-muted">Settled via Cash, UPI, Card, or Transfer</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-danger border-4 bg-white p-3">
            <div class="text-muted small fw-semibold text-uppercase">Total Outstanding Receivable</div>
            <h3 class="fw-bold my-1 text-danger">₹{{ number_format($totalOutstanding, 2) }}</h3>
            <small class="text-muted">Balance pending settlement</small>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card shadow-sm border-0 mb-4 bg-white no-print">
    <div class="card-body p-3">
        <form action="{{ route('reports.financial') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Invoice Date From</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Invoice Date To</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All --</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Unpaid" {{ request('status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('reports.financial') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>Beneficiary Guest</th>
                    <th>Subtotal</th>
                    <th>Taxes</th>
                    <th>Total</th>
                    <th>Collected</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td class="fw-bold"><a href="{{ route('billing.invoice', $inv) }}">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td>{{ $inv->booking->booking_reference }}</td>
                    <td>{{ $inv->booking->primaryGuest?->name ?? 'Guest' }}</td>
                    <td>₹{{ number_format($inv->subtotal, 2) }}</td>
                    <td>₹{{ number_format($inv->tax_total, 2) }}</td>
                    <td class="fw-bold">₹{{ number_format($inv->grand_total, 2) }}</td>
                    <td class="text-success fw-bold">₹{{ number_format($inv->paid_amount, 2) }}</td>
                    <td class="@if($inv->balance_due > 0) text-danger fw-bold @else text-muted @endif">₹{{ number_format($inv->balance_due, 2) }}</td>
                    <td><span class="badge @if($inv->status === 'Paid') bg-success @else bg-danger @endif">{{ $inv->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">No invoices found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

