@extends('layouts.app')

@section('title', 'System Audit Trail')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-primary me-2"></i> Immutable Institutional Audit Trail</h4>
        <small class="text-muted">Tamper-proof record of every configuration change, allocation, workflow decision, check-in/out, and financial action</small>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('oversight.audit') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Module</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">-- All Modules --</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search Keyword / Actor / IP</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search actor name, action, IP..." value="{{ request('search') }}">
            </div>

            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filter Logs
                </button>
                <a href="{{ route('oversight.audit') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Actor & Role</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Before & After State Values</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="white-space: nowrap;">{{ $log->logged_at->format('d M Y, H:i:s') }}</td>
                    <td>
                        <div class="fw-bold">{{ $log->actor_name ?? 'System' }}</div>
                        <small class="text-muted text-uppercase" style="font-size: 0.68rem;">{{ $log->actor_role ?? 'Auto' }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $log->module }}</span></td>
                    <td><strong class="text-primary">{{ $log->action }}</strong></td>
                    <td><code>{{ $log->ip_address }}</code></td>
                    <td>
                        @if($log->before_values || $log->after_values)
                            <button class="btn btn-sm btn-outline-secondary py-0" type="button" data-bs-toggle="collapse" data-bs-target="#log_{{ $log->id }}">
                                Inspect JSON Diffs
                            </button>
                            <div class="collapse mt-2" id="log_{{ $log->id }}">
                                <div class="bg-light p-2 rounded small">
                                    @if($log->before_values)
                                        <div class="text-danger fw-bold">Before:</div>
                                        <pre class="mb-1" style="font-size: 0.7rem;">{{ json_encode($log->before_values, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                    @if($log->after_values)
                                        <div class="text-success fw-bold">After:</div>
                                        <pre class="mb-0" style="font-size: 0.7rem;">{{ json_encode($log->after_values, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                </div>
                            </div>
                        @else
                            <span class="text-muted small">None</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No audit log records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection

