@extends('layouts.app')

@section('title', 'Category-Wise Tariff Matrix')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-currency-rupee text-success me-2"></i> Category-Wise Room Tariff Matrix</h4>
        <small class="text-muted">Configure differential per-night accommodation rates across guest classifications and room types</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Approved Tariff Matrix (INR per Night)</h6>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('masters.tariffs.update') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th class="text-start" style="width: 300px;">Guest Classification Category</th>
                            @foreach($roomTypes as $rt)
                                <th>
                                    <div>{{ $rt->name }}</div>
                                    <small class="text-warning" style="font-size: 0.72rem;">Base: ₹{{ $rt->default_tariff }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guestTypes as $gt)
                        <tr>
                            <td class="bg-light">
                                <div class="fw-bold text-dark">{{ $gt->name }}</div>
                                <small class="text-muted">{{ $gt->description }}</small>
                            </td>

                            @foreach($roomTypes as $rt)
                                @php
                                    $rate = $tariffs[$gt->id]->firstWhere('room_type_id', $rt->id)?->rate_per_night ?? $rt->default_tariff;
                                @endphp
                                <td style="max-width: 130px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" min="0" name="tariffs[{{ $gt->id }}][{{ $rt->id }}]" class="form-control text-end fw-bold" value="{{ $rate }}">
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Changes will immediately take effect for all future reservations. Past finalized invoices remain immutable.
                </span>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Save Tariff Matrix
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

