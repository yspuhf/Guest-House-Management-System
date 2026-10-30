@extends('layouts.app')

@section('title', 'Notification Delivery Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope-paper text-primary me-2"></i> Email Notification Delivery Register</h4>
        <small class="text-muted">Tracking of transactional notices dispatched to applicants and guests</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Recipient</th>
                    <th>Booking Ref</th>
                    <th>Event Key</th>
                    <th>Subject</th>
                    <th>Delivery Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                <tr>
                    <td style="white-space: nowrap;">{{ $n->sent_at->format('d M Y, H:i:s') }}</td>
                    <td>
                        <div class="fw-bold">{{ $n->recipient_name }}</div>
                        <small class="text-muted">{{ $n->recipient_email }}</small>
                    </td>
                    <td>
                        @if($n->booking)
                            <a href="{{ route('bookings.show', $n->booking) }}" class="fw-semibold">{{ $n->booking->booking_reference }}</a>
                        @else
                            <span class="text-muted">General</span>
                        @endif
                    </td>
                    <td><code>{{ $n->event }}</code></td>
                    <td class="fw-semibold">{{ $n->subject }}</td>
                    <td>
                        <span class="badge @if($n->delivery_status === 'Sent') bg-success @elseif($n->delivery_status === 'Queued') bg-warning text-dark @else bg-danger @endif">
                            {{ $n->delivery_status }}
                        </span>
                        @if($n->error_message)
                            <small class="text-danger d-block mt-1">{{ $n->error_message }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No notification logs recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

