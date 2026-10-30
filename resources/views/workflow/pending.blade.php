@extends('layouts.app')

@section('title', 'Institutional Approval Queue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-hourglass-split text-warning me-2"></i> Institutional Scrutiny & Approval Desk</h4>
        <small class="text-muted">Review, verify and sanction official accommodation requests</small>
    </div>
</div>

<!-- Nav Tabs: Verification vs Approval Queue -->
<ul class="nav nav-tabs mb-4" id="workflowTab" role="tablist">
    @if($isVerifier)
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="verification-tab" data-bs-toggle="tab" data-bs-target="#verification" type="button" role="tab">
            <i class="bi bi-check2-circle text-info me-1"></i> Departmental Verification
            <span class="badge bg-warning text-dark ms-2">{{ count($verifications) }}</span>
        </button>
    </li>
    @endif

    @if($isApprover)
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ !$isVerifier ? 'active' : '' }} fw-semibold" id="approval-tab" data-bs-toggle="tab" data-bs-target="#approval" type="button" role="tab">
            <i class="bi bi-award text-success me-1"></i> Authority Sanction / Approval
            <span class="badge bg-success ms-2">{{ count($approvals) }}</span>
        </button>
    </li>
    @endif
</ul>

<div class="tab-content" id="workflowTabContent">
    <!-- Verification Tab Pane -->
    @if($isVerifier)
    <div class="tab-pane fade show active" id="verification" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">Requests Awaiting Scrutiny & Verification</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Ref #</th>
                            <th>Applicant / Department</th>
                            <th>Primary Guest</th>
                            <th>Guest House & Room Type</th>
                            <th>Stay Dates (Nights)</th>
                            <th>Purpose / Sanction Document</th>
                            <th class="text-end">Verification Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verifications as $b)
                        <tr>
                            <td>
                                <a href="{{ route('bookings.show', $b) }}" class="fw-bold">{{ $b->booking_reference }}</a>
                                <div class="text-muted small">{{ $b->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $b->applicant->name }}</div>
                                <small class="text-muted">{{ $b->applicant->department }}</small>
                            </td>
                            <td>
                                <div>{{ $b->primaryGuest?->name ?? 'Guest' }}</div>
                                <small class="text-muted">{{ $b->guestType->name }}</small>
                            </td>
                            <td>
                                <div>{{ $b->guestHouse->name }}</div>
                                <small class="text-muted">{{ $b->roomType->name }} ({{ $b->rooms_requested }} room)</small>
                            </td>
                            <td>
                                <div>{{ $b->arrival_date->format('d M') }} &rarr; {{ $b->departure_date->format('d M Y') }}</div>
                                <small class="badge bg-light text-dark border">{{ $b->billable_nights }} Night(s)</small>
                            </td>
                            <td>
                                <div class="small text-truncate" style="max-width: 200px;">{{ $b->purpose }}</div>
                                @if($b->documents->count() > 0)
                                    <a href="{{ route('documents.download', $b->documents->first()) }}" class="badge bg-info-subtle text-info text-decoration-none mt-1">
                                        <i class="bi bi-paperclip"></i> View Attached Sanction
                                    </a>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-search me-1"></i> Review Dossier
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-all fs-2 d-block text-success mb-2"></i> All pending departmental verification requests are clear!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Approval Tab Pane -->
    @if($isApprover)
    <div class="tab-pane fade {{ !$isVerifier ? 'show active' : '' }}" id="approval" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">Requests Awaiting Final Authority Sanction</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Ref #</th>
                            <th>Applicant / Department</th>
                            <th>Primary Guest</th>
                            <th>Guest House & Room Type</th>
                            <th>Stay Dates (Nights)</th>
                            <th>Budget Head / Documents</th>
                            <th class="text-end">Sanction Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvals as $b)
                        <tr>
                            <td>
                                <a href="{{ route('bookings.show', $b) }}" class="fw-bold">{{ $b->booking_reference }}</a>
                                <div class="text-muted small">{{ $b->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $b->applicant->name }}</div>
                                <small class="text-muted">{{ $b->applicant->department }}</small>
                            </td>
                            <td>
                                <div>{{ $b->primaryGuest?->name ?? 'Guest' }}</div>
                                <small class="text-muted">{{ $b->guestType->name }}</small>
                            </td>
                            <td>
                                <div>{{ $b->guestHouse->name }}</div>
                                <small class="text-muted">{{ $b->roomType->name }} ({{ $b->rooms_requested }} room)</small>
                            </td>
                            <td>
                                <div>{{ $b->arrival_date->format('d M') }} &rarr; {{ $b->departure_date->format('d M Y') }}</div>
                                <small class="badge bg-light text-dark border">{{ $b->billable_nights }} Night(s)</small>
                            </td>
                            <td>
                                <div class="small fw-semibold text-success">{{ $b->expenditureHead?->name ?? 'Self-Paid by Guest' }}</div>
                                @if($b->documents->count() > 0)
                                    <a href="{{ route('documents.download', $b->documents->first()) }}" class="badge bg-info-subtle text-info text-decoration-none mt-1">
                                        <i class="bi bi-file-earmark-check"></i> Sanction Doc
                                    </a>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-award me-1"></i> Inspect & Sanction
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-all fs-2 d-block text-success mb-2"></i> All approval queues are clear! No pending requests.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

