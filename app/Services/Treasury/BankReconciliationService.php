<?php

declare(strict_types=1);

namespace App\Services\Treasury;

use App\Enums\Treasury\DocumentStatus;
use App\Enums\Treasury\MatchType;
use App\Enums\Treasury\ReconciliationStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Treasury\BankReconciliation;
use App\Models\Treasury\BankReconciliationMatch;
use App\Models\Treasury\BankStatement;
use App\Models\Treasury\BankStatementLine;
use App\Models\Treasury\CashTransaction;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityCheck;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Treasury\Exceptions\ReconciliationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Bank statement import → match → reconcile. INV-6:
 *   statement_balance + outstanding_deposits − outstanding_cheques = book_balance
 */
class BankReconciliationService
{
    public function __construct(
        private readonly TreasuryLedgerService $ledger,
        private readonly IntegrityService $integrity,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function importStatement(Company $company, TreasuryAccount $account, array $header, array $lines): BankStatement
    {
        if (! $account->isBank()) {
            throw new ReconciliationException('Bank statements apply only to bank treasury accounts.');
        }

        return DB::transaction(function () use ($company, $account, $header, $lines): BankStatement {
            $statement = BankStatement::create([
                'company_id' => $company->id,
                'treasury_account_id' => $account->id,
                'statement_number' => $header['statement_number'] ?? null,
                'statement_date' => $header['statement_date'] ?? ($header['period_end'] ?? now()->toDateString()),
                'period_start' => $header['period_start'] ?? null,
                'period_end' => $header['period_end'] ?? now()->toDateString(),
                'opening_balance' => Decimal::of(is_scalar($header['opening_balance'] ?? null) ? (string) $header['opening_balance'] : '0'),
                'closing_balance' => Decimal::of(is_scalar($header['closing_balance'] ?? null) ? (string) $header['closing_balance'] : '0'),
                'currency' => is_string($header['currency'] ?? null) ? $header['currency'] : $account->currency,
                'status' => 'imported',
                'source' => is_string($header['source'] ?? null) ? $header['source'] : 'manual',
            ]);

            $lineNo = 0;
            foreach ($lines as $line) {
                $lineNo++;
                $amount = Decimal::of(is_scalar($line['amount'] ?? null) ? (string) $line['amount'] : '0');
                $direction = is_string($line['direction'] ?? null)
                    ? $line['direction']
                    : (Decimal::isNegative($amount) ? 'out' : 'in');
                $abs = Decimal::isNegative($amount) ? Decimal::sub('0', $amount) : $amount;

                $statement->lines()->create([
                    'line_no' => $lineNo,
                    'line_date' => $line['line_date'] ?? $statement->period_end,
                    'reference' => $line['reference'] ?? null,
                    'description' => $line['description'] ?? null,
                    'amount' => $direction === 'out' ? Decimal::sub('0', $abs) : $abs,
                    'direction' => $direction,
                    'is_matched' => false,
                ]);
            }

            $this->audit->record($statement, 'imported', $company->id, null, [
                'lines' => $lineNo,
                'closing_balance' => $statement->closing_balance,
            ]);

            return $statement->load('lines');
        });
    }

    public function start(Company $company, AccountingBook $book, TreasuryAccount $account, BankStatement $statement, int $dateToleranceDays = 3): BankReconciliation
    {
        $asOf = Carbon::parse($statement->period_end);
        $bookBalance = $this->ledger->bookBalance($account, $asOf);

        $recon = BankReconciliation::create([
            'company_id' => $company->id,
            'book_id' => $book->id,
            'treasury_account_id' => $account->id,
            'bank_statement_id' => $statement->id,
            'as_of_date' => $asOf->toDateString(),
            'statement_balance' => $statement->closing_balance,
            'book_balance' => $bookBalance,
            'status' => ReconciliationStatus::IN_PROGRESS,
            'date_tolerance_days' => $dateToleranceDays,
        ]);

        $this->audit->record($recon, 'started', $company->id, null, ['statement_id' => $statement->id]);

        return $recon;
    }

    /** Auto-match: exact (date+amount+ref) → reference → amount within date tolerance. */
    public function autoMatch(BankReconciliation $recon): int
    {
        $recon->loadMissing('statement.lines', 'treasuryAccount');
        $tolerance = $recon->date_tolerance_days;
        $matched = 0;

        $candidates = CashTransaction::query()
            ->where('treasury_account_id', $recon->treasury_account_id)
            ->where('status', DocumentStatus::POSTED->value)
            ->where('is_cleared', false)
            ->get();

        foreach ($recon->statement->lines->where('is_matched', false) as $line) {
            $lineDate = Carbon::parse($line->line_date);
            $lineAbs = Decimal::isNegative(Decimal::of($line->amount))
                ? Decimal::sub('0', Decimal::of($line->amount))
                : Decimal::of($line->amount);
            $lineDir = $line->direction;

            $best = null;
            $bestType = null;

            foreach ($candidates as $tx) {
                if ($tx->is_cleared) {
                    continue;
                }
                $txAbs = Decimal::of($tx->amount);
                $txDir = $tx->direction;
                if ($txDir !== $lineDir || ! Decimal::equals($txAbs, $lineAbs)) {
                    continue;
                }
                $txDate = Carbon::parse($tx->transaction_date);
                $days = abs($txDate->diffInDays($lineDate));
                if ($days > $tolerance) {
                    continue;
                }

                $refMatch = $line->reference !== null && $tx->reference !== null && $line->reference === $tx->reference;
                $exactDate = $txDate->toDateString() === $lineDate->toDateString();

                if ($refMatch && $exactDate) {
                    $best = $tx;
                    $bestType = MatchType::EXACT;
                    break;
                }
                if ($refMatch && $bestType !== MatchType::EXACT) {
                    $best = $tx;
                    $bestType = MatchType::REFERENCE;
                } elseif ($best === null) {
                    $best = $tx;
                    $bestType = $exactDate ? MatchType::EXACT : MatchType::AMOUNT;
                }
            }

            if ($best !== null && $bestType !== null) {
                $this->matchManual($recon, $line, $best, $bestType);
                $matched++;
            }
        }

        return $matched;
    }

    public function matchManual(
        BankReconciliation $recon,
        BankStatementLine $line,
        CashTransaction $tx,
        MatchType $type = MatchType::MANUAL,
    ): BankReconciliationMatch {
        return DB::transaction(function () use ($recon, $line, $tx, $type): BankReconciliationMatch {
            if ($line->is_matched) {
                throw new ReconciliationException('Statement line is already matched.');
            }
            if ($tx->is_cleared) {
                throw new ReconciliationException('Cash transaction is already cleared.');
            }
            if ((int) $tx->treasury_account_id !== (int) $recon->treasury_account_id) {
                throw new ReconciliationException('Transaction does not belong to this bank account.');
            }

            $abs = Decimal::isNegative(Decimal::of($line->amount))
                ? Decimal::sub('0', Decimal::of($line->amount))
                : Decimal::of($line->amount);

            $match = BankReconciliationMatch::create([
                'bank_reconciliation_id' => $recon->id,
                'bank_statement_line_id' => $line->id,
                'cash_transaction_id' => $tx->id,
                'match_type' => $type,
                'amount' => $abs,
            ]);

            $line->forceFill(['is_matched' => true])->save();
            $tx->forceFill([
                'is_cleared' => true,
                'cleared_date' => $recon->as_of_date,
            ])->save();

            $this->audit->record($match, 'matched', $recon->company_id, null, [
                'type' => $type->value,
                'tx_id' => $tx->id,
                'line_id' => $line->id,
            ]);

            return $match;
        });
    }

    public function refresh(BankReconciliation $recon): BankReconciliation
    {
        $recon->loadMissing('treasuryAccount', 'statement');
        $account = $recon->treasuryAccount;
        $asOf = Carbon::parse($recon->as_of_date);

        $book = $this->ledger->bookBalance($account, $asOf);
        $deposits = $this->ledger->outstandingDepositsTotal($account, $asOf);
        $cheques = $this->ledger->outstandingChequesTotal($account, $asOf);
        $statement = Decimal::of($recon->statement->closing_balance);
        $adjusted = Decimal::sub(Decimal::add($statement, $deposits), $cheques);
        $difference = Decimal::sub($adjusted, $book);

        $recon->forceFill([
            'statement_balance' => $statement,
            'book_balance' => $book,
            'outstanding_deposits' => $deposits,
            'outstanding_cheques' => $cheques,
            'adjusted_statement_balance' => $adjusted,
            'difference' => $difference,
        ])->save();

        return $recon;
    }

    /** Complete only when INV-6 holds (adjusted statement = book). */
    public function complete(BankReconciliation $recon, ?int $actorId = null): BankReconciliation
    {
        $recon = $this->refresh($recon);

        if (! Decimal::equals(Decimal::of($recon->difference), '0')) {
            throw ReconciliationException::mismatch(
                (string) $recon->adjusted_statement_balance,
                (string) $recon->book_balance,
            );
        }

        $recon->forceFill([
            'status' => ReconciliationStatus::COMPLETED,
            'completed_by' => $actorId,
            'completed_at' => now(),
        ])->save();

        $recon->statement->forceFill(['status' => 'reconciled'])->save();

        $this->audit->record($recon, 'completed', $recon->company_id, null, [
            'book_balance' => $recon->book_balance,
            'adjusted_statement_balance' => $recon->adjusted_statement_balance,
        ]);

        return $recon;
    }

    /** Assert INV-6 for a treasury bank account (book = GL, and optional completed recon). */
    public function assert(Company $company, AccountingBook $book, TreasuryAccount $account, ?Carbon $asOf = null): IntegrityCheck
    {
        $bookBal = $this->ledger->bookBalance($account, $asOf);
        $check = $this->integrity->controlEqualsSubledger(
            $company,
            $book,
            $account->gl_account_code,
            $bookBal,
            'INV-6',
            $asOf,
        );

        if ($check->failed()) {
            throw new IntegrityViolationException([$check]);
        }

        return $check;
    }
}
