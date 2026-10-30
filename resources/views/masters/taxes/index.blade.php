@extends('layouts.app')

@section('title', 'Tax Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-percent text-primary me-2"></i> Taxes & Levies Master</h4>
        <small class="text-muted">Configure GST components (CGST, SGST, IGST) or fixed hospitality surcharges</small>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newTaxModal">
        <i class="bi bi-plus-circle me-1"></i> Add Tax
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Tax Name & Code</th>
                    <th>Calculation Method</th>
                    <th>Rate (%)</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taxes as $tax)
                <tr>
                    <td>
                        <div class="fw-bold fs-6">{{ $tax->name }}</div>
                        <code>{{ $tax->code }}</code>
                    </td>
                    <td><span class="badge bg-light text-dark border text-capitalize">{{ $tax->calculation_method }}</span></td>
                    <td class="fw-bold fs-6 text-primary">{{ $tax->rate }}%</td>
                    <td>{{ $tax->description ?? 'N/A' }}</td>
                    <td>
                        <span class="badge @if($tax->is_active) bg-success @else bg-danger @endif">
                            {{ $tax->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTax_{{ $tax->id }}">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editTax_{{ $tax->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('masters.taxes.update', $tax) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Edit Tax: {{ $tax->name }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Tax Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $tax->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Rate (%)</label>
                                        <input type="number" step="0.01" name="rate" class="form-control" value="{{ $tax->rate }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ $tax->description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" {{ $tax->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$tax->is_active ? 'selected' : '' }}>Inactive</option>
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
                    <td colspan="6" class="text-center py-5 text-muted">No taxes configured.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- New Tax Modal -->
<div class="modal fade" id="newTaxModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('masters.taxes.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-percent me-2"></i> Add Tax Rate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. CGST-9" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Central GST (9%)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Calculation Method</label>
                        <select name="calculation_method" class="form-select" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (INR)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rate <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="rate" class="form-control" placeholder="9.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tax</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

