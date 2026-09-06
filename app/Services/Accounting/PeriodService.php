<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\PeriodStatus;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\User;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Gl\Exceptions\PeriodCloseException;
use App\Services\Security\AccessControl;
use Illuminate\Support\Carbon;

class PeriodService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly AccessControl $access,
    ) {}

    /** Find the fiscal period whose date range contains the given date. */
    public function forDate(Company $company, Carbon $date): ?FiscalPeriod
    {
        return FiscalPeriod::query()
            ->where('company_id', $company->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->orderByDesc('is_adjustment') // prefer adjustment period when overlapping
            ->first();
    }

    /**
     * Resolve the period a posting must land in, enforcing the period lock
     * (spec §26). Throws when no OPEN period covers the date.
     */
    public function resolvePostable(Company $company, Carbon $date, bool $allowSoftClosed = false): FiscalPeriod
    {
        $period = $this->forDate($company, $date);

        if ($period === null) {
            throw PostingException::closedPeriod($date->toDateString());
        }

        $ok = $allowSoftClosed
            ? $period->status->allowsPrivilegedPosting()
            : $period->status->allowsPosting();

        if (! $ok) {
            throw PostingException::closedPeriod($date->toDateString());
        }

        return $period;
    }

    public function close(FiscalPeriod $period, PeriodStatus $status, ?int $userId = null): FiscalPeriod
    {
        $period->update([
            'status' => $status,
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);

        return $period;
    }

    public function softClose(FiscalPeriod $period, ?int $userId = null): FiscalPeriod
    {
        return $this->transition($period, PeriodStatus::SOFT_CLOSED, $userId);
    }

    public function hardClose(FiscalPeriod $period, ?int $userId = null): FiscalPeriod
    {
        return $this->transition($period, PeriodStatus::HARD_CLOSED, $userId);
    }

    public function lock(FiscalPeriod $period, ?int $userId = null): FiscalPeriod
    {
        return $this->transition($period, PeriodStatus::LOCKED, $userId);
    }

    public function reopen(FiscalPeriod $period, string $reason, ?int $userId = null): FiscalPeriod
    {
        if ($period->status === PeriodStatus::LOCKED) {
            throw PeriodCloseException::reopenLocked();
        }
        if ($period->status === PeriodStatus::OPEN) {
            throw PeriodCloseException::invalidTransition($period->status->value, PeriodStatus::OPEN->value);
        }
        if (trim($reason) === '') {
            throw PeriodCloseException::reopenRequiresReason();
        }
        if ($userId !== null) {
            $actor = User::query()->find($userId);
            if ($actor instanceof User) {
                $this->access->assert($actor, $period->company, 'period.reopen');
            }
        }

        $from = $period->status->value;
        $period->update([
            'status' => PeriodStatus::OPEN,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        $this->audit->record($period, 'reopened', $period->company_id, ['status' => $from], [
            'status' => PeriodStatus::OPEN->value,
            'reason' => $reason,
            'actor_id' => $userId,
        ], $reason);

        return $period->fresh() ?? $period;
    }

    private function transition(FiscalPeriod $period, PeriodStatus $to, ?int $userId): FiscalPeriod
    {
        $from = $period->status;
        $allowed = match ($to) {
            PeriodStatus::SOFT_CLOSED => $from === PeriodStatus::OPEN,
            PeriodStatus::HARD_CLOSED => in_array($from, [PeriodStatus::OPEN, PeriodStatus::SOFT_CLOSED], true),
            PeriodStatus::LOCKED => in_array($from, [PeriodStatus::HARD_CLOSED, PeriodStatus::SOFT_CLOSED], true),
            default => false,
        };

        if (! $allowed) {
            throw PeriodCloseException::invalidTransition($from->value, $to->value);
        }

        $period->update([
            'status' => $to,
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);

        $this->audit->record($period, 'closed', $period->company_id, ['status' => $from->value], [
            'status' => $to->value,
            'actor_id' => $userId,
        ]);

        return $period->fresh() ?? $period;
    }
}
