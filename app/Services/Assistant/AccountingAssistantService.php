<?php

declare(strict_types=1);

namespace App\Services\Assistant;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\Assistant\AssistantDraft;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Gl\ManualJournalService;

class AccountingAssistantService
{
    public function __construct(
        private readonly ManualJournalService $manual,
    ) {}

    /**
     * @param  list<array{account_id: int, debit?: string|float|int, credit?: string|float|int}>  $lines
     */
    public function propose(Company $company, AccountingBook $book, string $prompt, array $lines): AssistantDraft
    {
        return AssistantDraft::create([
            'company_id' => $company->id,
            'book_id' => $book->id,
            'prompt' => $prompt,
            'status' => 'draft',
            'proposed_lines' => $lines,
        ]);
    }

    public function review(AssistantDraft $draft): AssistantDraft
    {
        $draft->forceFill(['status' => 'reviewed'])->save();

        return $draft;
    }

    public function approve(AssistantDraft $draft): AssistantDraft
    {
        if ($draft->status !== 'reviewed' && $draft->status !== 'draft') {
            throw new PostingException('Assistant draft is not reviewable.');
        }
        $draft->forceFill(['status' => 'approved'])->save();

        return $draft;
    }

    public function post(AssistantDraft $draft): Journal
    {
        if ($draft->status !== 'approved') {
            throw new PostingException('Assistant draft must be approved before posting.');
        }
        $raw = $draft->proposed_lines;
        if ($raw === []) {
            throw new PostingException('Assistant draft has no proposed lines.');
        }
        $inputs = [];
        foreach ($raw as $line) {
            if (! isset($line['account_id'])) {
                throw new PostingException('Assistant draft line is invalid.');
            }
            $debit = 0;
            $credit = 0;
            if (isset($line['debit']) && (is_int($line['debit']) || is_float($line['debit']) || is_string($line['debit']))) {
                $debit = $line['debit'];
            }
            if (isset($line['credit']) && (is_int($line['credit']) || is_float($line['credit']) || is_string($line['credit']))) {
                $credit = $line['credit'];
            }
            $inputs[] = new LineInput(
                accountId: (int) $line['account_id'],
                debit: $debit,
                credit: $credit,
            );
        }
        $journal = $this->manual->createDraft($draft->company, $draft->book, [
            'journal_date' => now()->toDateString(),
            'description' => 'AI assistant: '.$draft->prompt,
            'source' => 'manual',
        ], $inputs);
        $posted = $this->manual->post($journal);
        $draft->forceFill(['status' => 'posted', 'journal_id' => $posted->id])->save();

        return $posted;
    }
}
