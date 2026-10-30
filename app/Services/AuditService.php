<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action into the tamper-proof audit trail.
     */
    public function log(
        ?User $actor,
        string $action,
        string $module,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?array $before = null,
        ?array $after = null
    ): ?AuditLog {
        try {
            return AuditLog::create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name ?? 'System',
                'actor_role' => $actor?->primary_role ?? 'system',
                'action' => $action,
                'module' => $module,
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'before_values' => $before,
                'after_values' => $after,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'CLI / Localhost',
                'logged_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit log write failure must not abort critical transactional flow, but log to error log
            logger()->error('Audit log write failed: ' . $e->getMessage());
            return null;
        }
    }
}

