@php
    $statusMap = [
        'Draft' => ['bg' => 'secondary', 'icon' => 'pencil-square'],
        'Submitted' => ['bg' => 'info text-dark', 'icon' => 'send'],
        'Verification Pending' => ['bg' => 'warning text-dark', 'icon' => 'hourglass-split'],
        'Approval Pending' => ['bg' => 'primary', 'icon' => 'clock-history'],
        'Approved' => ['bg' => 'success', 'icon' => 'check2-circle'],
        'Room Allocated' => ['bg' => 'info text-dark', 'icon' => 'door-open-fill'],
        'Checked In' => ['bg' => 'success', 'icon' => 'person-check-fill'],
        'Checked Out' => ['bg' => 'secondary', 'icon' => 'box-arrow-right'],
        'Closed' => ['bg' => 'dark', 'icon' => 'check-all'],
        'Rejected' => ['bg' => 'danger', 'icon' => 'x-octagon-fill'],
        'Returned for Correction' => ['bg' => 'warning text-dark', 'icon' => 'arrow-counterclockwise'],
        'Cancelled' => ['bg' => 'secondary', 'icon' => 'ban'],
        'Extension Pending' => ['bg' => 'warning text-dark', 'icon' => 'calendar-plus'],
    ];
    $current = $statusMap[$status] ?? ['bg' => 'secondary', 'icon' => 'tag'];
@endphp

<span class="badge bg-{{ $current['bg'] }} badge-status shadow-sm">
    <i class="bi bi-{{ $current['icon'] }} me-1"></i> {{ $status }}
</span>

