<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Enums\Accounting\JournalStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Security\AccessControl;
use Illuminate\Support\Facades\DB;

/**
 * Manual journal workflow: Draft → Pending → Approved → Posted.
 */
class ManualJournalService
{
    public function __construct(
        private readonly JournalService $journals,
        private readonly AuditLogger $audit,
        private readonly AccessControl $access,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, LineInput>  $lines
     */
    public function createDraft(Company $company, AccountingBook $book, array $header, array $lines): Journal
    {
        $header['source'] = $header['source'] ?? 'manual';
        $header['is_system_generated'] = false;

        return $this->journals->createDraft($company, $book, $header, $lines);
    }

    public function submit(Journal $journal, ?User $actor = null): Journal
    {
        $this->assertManual($journal);
        if ($journal->status !== JournalStatus::DRAFT) {
            throw new PostingException('Only draft journals can be submitted for approval.');
        }

        $journal->forceFill(['status' => JournalStatus::PENDING])->save();
        $this->audit->record($journal, 'submitted', $journal->company_id, null, [
            'actor_id' => $actor?->id,
        ]);

        return $journal;
    }

    public function approve(Journal $journal, ?User $actor = null): Journal
    {
        $this->assertManual($journal);
        if ($journal->status !== JournalStatus::PENDING && $journal->status !== JournalStatus::DRAFT) {
            throw new PostingException('Only draft or pending journals can be approved.');
        }
        $this->access->assert($actor, $journal->company, 'journal.approve');
        if ($actor !== null && $journal->created_by !== null) {
            $this->access->assertNotSelf((int) $journal->created_by, $actor);
        }

        $journal->forceFill([
            'status' => JournalStatus::APPROVED,
            'approved_by' => $actor?->id,
        ])->save();
        $this->audit->record($journal, 'approved', $journal->company_id, null, [
            'actor_id' => $actor?->id,
        ]);

        return $journal;
    }

    public function post(Journal $journal, ?User $poster = null, bool $allowSoftClosed = false): Journal
    {
        $this->assertManual($journal);
        if (! in_array($journal->status, [JournalStatus::DRAFT, JournalStatus::PENDING, JournalStatus::APPROVED], true)) {
            throw new PostingException('Journal is not in a postable status.');
        }

        $this->access->assert($poster, $journal->company, 'journal.post');
        if ($poster !== null && $journal->created_by !== null) {
            $this->access->assertNotSelf((int) $journal->created_by, $poster);
        }

        return $this->journals->post($journal, $poster, $allowSoftClosed);
    }

    public function void(Journal $journal, ?User $actor = null, ?string $reason = null): Journal
    {
        $this->assertManual($journal);
        if (! in_array($journal->status, [JournalStatus::DRAFT, JournalStatus::PENDING, JournalStatus::APPROVED], true)) {
            throw new PostingException('Only unposted journals can be voided.');
        }

        return DB::transaction(function () use ($journal, $actor, $reason): Journal {
            $journal->forceFill(['status' => JournalStatus::VOID])->save();
            $this->audit->record($journal, 'voided', $journal->company_id, null, [
                'actor_id' => $actor?->id,
                'reason' => $reason,
            ], $reason);

            return $journal;
        });
    }

    private function assertManual(Journal $journal): void
    {
        if ($journal->source !== 'manual' || $journal->is_system_generated) {
            throw new PostingException('ManualJournalService only operates on user-entered manual journals.');
        }
    }
}
