<?php

declare(strict_types=1);

namespace App\Services\Treasury;

use App\Enums\Treasury\CashTransactionType;
use App\Enums\Treasury\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Treasury\CashTransaction;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Treasury\Exceptions\DuplicateDocumentException;
use App\Services\Treasury\Support\TreasuryGuards;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Cash & bank movements. Every post runs through the Accounting Engine.
 */
class CashTransactionService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly JournalService $journals,
        private readonly AuditLogger $audit,
        private readonly ControlAccountResolver $controls,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     */
    public function createDraft(
        Company $company,
        AccountingBook $book,
        TreasuryAccount $account,
        CashTransactionType $type,
        string|float|int $amount,
        array $header = [],
    ): CashTransaction {
        if (! $account->is_active || $account->status !== 'active') {
            throw new PostingException("Treasury account {$account->code} is not active.");
        }

        $amt = TreasuryGuards::assertPositiveAmount($amount);
        $currency = is_string($header['currency'] ?? null) ? $header['currency'] : $account->currency;
        TreasuryGuards::assertCurrency($currency);
        $rate = TreasuryGuards::assertExchangeRate($header['exchange_rate'] ?? 1);

        $counterTreasuryId = isset($header['counter_treasury_account_id']) ? (int) $header['counter_treasury_account_id'] : null;
        $counterAccount = is_string($header['counter_account_code'] ?? null) ? $header['counter_account_code'] : null;

        if ($type === CashTransactionType::TRANSFER) {
            if ($counterTreasuryId === null) {
                throw new PostingException('A transfer requires counter_treasury_account_id (destination).');
            }
        } elseif (in_array($type, [
            CashTransactionType::MISC_RECEIPT,
            CashTransactionType::MISC_PAYMENT,
            CashTransactionType::CASH_RECEIPT,
            CashTransactionType::CASH_PAYMENT,
            CashTransactionType::BANK_RECEIPT,
            CashTransactionType::BANK_PAYMENT,
            CashTransactionType::BANK_FEE,
            CashTransactionType::BANK_INTEREST,
        ], true)) {
            if ($counterAccount === null || $counterAccount === '') {
                $counterAccount = match ($type) {
                    CashTransactionType::BANK_FEE => '630102',
                    CashTransactionType::BANK_INTEREST => '420101',
                    default => throw new PostingException('counter_account_code is required for this transaction type.'),
                };
            }
            $this->controls->assertPostingAccount($company, $counterAccount);
        }

        $direction = $type === CashTransactionType::TRANSFER || $type->isOutbound() ? 'out' : 'in';
        if ($type === CashTransactionType::TRANSFER) {
            $direction = 'out'; // from the source account's perspective
        }

        return DB::transaction(function () use ($company, $book, $account, $type, $amt, $header, $currency, $rate, $counterTreasuryId, $counterAccount, $direction): CashTransaction {
            $number = isset($header['number']) ? (string) $header['number'] : null;
            if ($number !== null && CashTransaction::where('company_id', $company->id)->where('number', $number)->exists()) {
                throw DuplicateDocumentException::make('cash transaction', $number);
            }

            $tx = CashTransaction::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'treasury_account_id' => $account->id,
                'counter_treasury_account_id' => $counterTreasuryId,
                'number' => $number,
                'type' => $type,
                'transaction_date' => $header['transaction_date'] ?? now()->toDateString(),
                'currency' => $currency,
                'exchange_rate' => $rate,
                'amount' => $amt,
                'direction' => $direction,
                'counter_account_code' => $counterAccount,
                'reference' => $header['reference'] ?? null,
                'cheque_number' => $header['cheque_number'] ?? null,
                'value_date' => $header['value_date'] ?? null,
                'description' => $header['description'] ?? null,
                'status' => DocumentStatus::DRAFT,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $this->audit->record($tx, 'created', $company->id, null, [
                'type' => $type->value,
                'amount' => $amt,
            ]);

            return $tx;
        });
    }

    public function post(CashTransaction $tx, ?int $posterId = null): CashTransaction
    {
        if (! $tx->status->isMutable()) {
            throw new PostingException('Cash transaction is already posted; reverse it instead of editing.');
        }

        return DB::transaction(function () use ($tx, $posterId): CashTransaction {
            $tx->loadMissing('treasuryAccount', 'counterTreasuryAccount', 'book', 'company');
            $number = $tx->number ?: $this->nextNumber($tx->company_id, Carbon::parse($tx->transaction_date)->year, $tx->type);

            $document = $this->buildDocument($tx, $number);
            $journal = $this->engine->postFrom($tx->company, $document);

            $tx->forceFill([
                'number' => $number,
                'status' => DocumentStatus::POSTED,
                'journal_id' => $journal->id,
                'posted_by' => $posterId,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($tx, 'posted', $tx->company_id, null, [
                'journal_id' => $journal->id,
                'number' => $number,
            ]);

            return $tx;
        });
    }

    public function reverse(CashTransaction $tx, ?int $actorId = null, ?string $reason = null): CashTransaction
    {
        if (! $tx->status->isPosted()) {
            throw new PostingException('Only a posted cash transaction can be reversed.');
        }
        if ($tx->is_cleared) {
            throw new PostingException('Cannot reverse a cleared (reconciled) cash transaction.');
        }

        return DB::transaction(function () use ($tx, $actorId, $reason): CashTransaction {
            $tx->loadMissing('journal', 'company');
            $journal = $tx->journal ?? throw new PostingException('Posted transaction is missing its journal.');
            $reversal = $this->journals->reverse($journal, reason: $reason);

            $tx->forceFill([
                'status' => DocumentStatus::REVERSED,
                'reversed_by_journal_id' => $reversal->id,
            ])->save();

            $this->audit->record($tx, 'reversed', $tx->company_id, null, [
                'reversal_journal_id' => $reversal->id,
                'actor_id' => $actorId,
            ], $reason);

            return $tx;
        });
    }

    private function buildDocument(CashTransaction $tx, string $number): GenericSourceDocument
    {
        $account = $tx->treasuryAccount;
        $rate = (float) $tx->exchange_rate;
        $basis = $tx->book->basis->value;
        $amount = (string) $tx->amount;

        if ($tx->type === CashTransactionType::TRANSFER) {
            $dest = $tx->counterTreasuryAccount
                ?? throw new PostingException('Transfer is missing the destination treasury account.');

            return new GenericSourceDocument(
                type: 'treasury.transfer',
                date: Carbon::parse($tx->transaction_date)->toDateString(),
                currency: $tx->currency,
                reference: $number,
                payload: [
                    'from_account' => $account->gl_account_code,
                    'to_account' => $dest->gl_account_code,
                    'amount' => $amount,
                    'book_basis' => $basis,
                    'exchange_rate' => $rate,
                ],
            );
        }

        $counter = (string) $tx->counter_account_code;
        $inbound = $tx->type->isInbound();

        return new GenericSourceDocument(
            type: $inbound ? 'treasury.receipt' : 'treasury.payment',
            date: Carbon::parse($tx->transaction_date)->toDateString(),
            currency: $tx->currency,
            reference: $number,
            payload: [
                'treasury_account' => $account->gl_account_code,
                'counter_account' => $counter,
                'amount' => $amount,
                'book_basis' => $basis,
                'exchange_rate' => $rate,
            ],
        );
    }

    private function nextNumber(int $companyId, int $year, CashTransactionType $type): string
    {
        $seq = CashTransaction::where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('transaction_date', $year)
            ->count() + 1;

        $prefix = match ($type) {
            CashTransactionType::TRANSFER => 'XFER',
            CashTransactionType::BANK_FEE => 'BFEE',
            CashTransactionType::BANK_INTEREST => 'BINT',
            default => $type->isInbound() ? 'TREC' : 'TPAY',
        };

        return sprintf('%s-%d-%05d', $prefix, $year, $seq);
    }
}
