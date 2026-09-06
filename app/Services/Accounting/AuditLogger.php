<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Accounting\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Writes the immutable audit trail (spec §31). Every material accounting event
 * is recorded with who/what/when/before/after and request context.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function record(
        Model $auditable,
        string $event,
        ?int $companyId = null,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
    ): AuditLog {
        return AuditLog::create([
            'company_id' => $companyId ?? $auditable->getAttribute('company_id'),
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'reason' => $reason,
            'ip_address' => $this->safeIp(),
            'user_agent' => $this->safeUserAgent(),
            'created_at' => now(),
        ]);
    }

    private function safeIp(): ?string
    {
        try {
            return Request::ip();
        } catch (\Throwable) {
            return null; // running outside an HTTP request (console/queue/test)
        }
    }

    private function safeUserAgent(): ?string
    {
        try {
            return substr((string) Request::userAgent(), 0, 255) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
