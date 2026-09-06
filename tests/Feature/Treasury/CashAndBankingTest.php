<?php

declare(strict_types=1);

use App\Enums\Treasury\CashTransactionType;
use App\Enums\Treasury\DocumentStatus;
use App\Enums\Treasury\ReconciliationStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\AuditLog;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Treasury\BankAccountService;
use App\Services\Treasury\BankReconciliationService;
use App\Services\Treasury\CashControlService;
use App\Services\Treasury\CashTransactionService;
use App\Services\Treasury\Exceptions\DuplicateDocumentException;
use App\Services\Treasury\Exceptions\ReconciliationException;
use App\Services\Treasury\TreasuryLedgerService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail()->id;

    $this->accounts = app(BankAccountService::class);
    $this->txs = app(CashTransactionService::class);
    $this->ledger = app(TreasuryLedgerService::class);
    $this->recon = app(BankReconciliationService::class);
    $this->cash = app(CashControlService::class);
    $this->controls = app(ControlAccountResolver::class);

    $this->bank = $this->accounts->createBank($this->company, ['code' => 'BNK-1', 'name_ar' => 'مصرف الوحدة']);
    $this->bankAccount = $this->accounts->createAccount($this->company, [
        'code' => 'BA-MAIN',
        'name_ar' => 'الحساب الرئيسي',
        'type' => 'bank',
        'bank_id' => $this->bank->id,
        'gl_account_code' => $this->controls->defaultBankCode($this->company),
        'account_number' => '001122',
        'opening_balance' => 0,
    ]);
    $this->cashAccount = $this->accounts->createAccount($this->company, [
        'code' => 'CA-PETTY',
        'name_ar' => 'الصندوق',
        'type' => 'cash',
        'gl_account_code' => $this->controls->defaultCashCode($this->company),
        'opening_balance' => 0,
    ]);
});

function tline($journal, callable $acc, string $code)
{
    return $journal->lines->firstWhere('account_id', $acc($code));
}

it('creates bank and cash accounts linked to chart GL codes', function () {
    expect($this->bankAccount->gl_account_code)->toBe($this->controls->defaultBankCode($this->company))
        ->and($this->cashAccount->gl_account_code)->toBe($this->controls->defaultCashCode($this->company))
        ->and(Account::where('code', $this->bankAccount->gl_account_code)->where('is_bank_account', true)->exists())->toBeTrue();
});

it('posts bank receipt and payment through the engine keeping INV-6', function () {
    $receipt = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        1000,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101', 'reference' => 'DEP-1'],
    ));
    $rj = $receipt->journal()->with('lines')->first();
    expect($receipt->status)->toBe(DocumentStatus::POSTED)
        ->and($receipt->journal_id)->not->toBeNull()
        ->and((float) tline($rj, $this->acc, $this->bankAccount->gl_account_code)->debit)->toBe(1000.0)
        ->and((float) tline($rj, $this->acc, '410101')->credit)->toBe(1000.0);

    $payment = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_PAYMENT,
        300,
        ['transaction_date' => '2026-03-12', 'counter_account_code' => '620201', 'reference' => 'CHQ-9', 'cheque_number' => '9'],
    ));
    expect((float) tline($payment->journal()->with('lines')->first(), $this->acc, $this->bankAccount->gl_account_code)->credit)->toBe(300.0);

    expect((float) $this->ledger->bookBalance($this->bankAccount))->toBe(700.0);
    $check = $this->recon->assert($this->company, $this->book, $this->bankAccount);
    expect($check->passed())->toBeTrue()->and((float) $check->expected)->toBe(700.0);

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();
    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest($this->company->id, $this->book->id));
    expect($bs['balanced'])->toBeTrue();
    expect(app(IntegrityService::class)->check($this->company, $this->book)['passed'])->toBeTrue();
});

it('transfers between cash and bank with a balanced journal', function () {
    $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->cashAccount,
        CashTransactionType::CASH_RECEIPT,
        500,
        ['transaction_date' => '2026-03-05', 'counter_account_code' => '410101'],
    ));

    $xfer = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->cashAccount,
        CashTransactionType::TRANSFER,
        200,
        [
            'transaction_date' => '2026-03-06',
            'counter_treasury_account_id' => $this->bankAccount->id,
            'reference' => 'TO-BANK',
        ],
    ));

    $j = $xfer->journal()->with('lines')->first();
    expect($j->isBalanced())->toBeTrue()
        ->and((float) tline($j, $this->acc, $this->bankAccount->gl_account_code)->debit)->toBe(200.0)
        ->and((float) tline($j, $this->acc, $this->cashAccount->gl_account_code)->credit)->toBe(200.0)
        ->and((float) $this->ledger->bookBalance($this->cashAccount))->toBe(300.0)
        ->and((float) $this->ledger->bookBalance($this->bankAccount))->toBe(200.0);

    $this->recon->assert($this->company, $this->book, $this->bankAccount);
    $this->recon->assert($this->company, $this->book, $this->cashAccount);
});

it('posts bank fees and interest through the engine', function () {
    $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        1000,
        ['transaction_date' => '2026-03-01', 'counter_account_code' => '410101'],
    ));

    $fee = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_FEE,
        25,
        ['transaction_date' => '2026-03-15'],
    ));
    $interest = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_INTEREST,
        10,
        ['transaction_date' => '2026-03-16'],
    ));

    expect((float) tline($fee->journal()->with('lines')->first(), $this->acc, '630102')->debit)->toBe(25.0)
        ->and((float) tline($interest->journal()->with('lines')->first(), $this->acc, '420101')->credit)->toBe(10.0)
        ->and((float) $this->ledger->bookBalance($this->bankAccount))->toBe(985.0);

    $this->recon->assert($this->company, $this->book, $this->bankAccount);
});

it('reconciles a bank statement to the book balance (INV-6)', function () {
    $dep = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        1000,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101', 'reference' => 'DEP-1'],
    ));
    $chq = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_PAYMENT,
        300,
        ['transaction_date' => '2026-03-12', 'counter_account_code' => '620201', 'reference' => 'CHQ-9'],
    ));

    // Statement shows only the deposit cleared; cheque still outstanding.
    $statement = $this->recon->importStatement($this->company, $this->bankAccount, [
        'period_end' => '2026-03-31',
        'closing_balance' => 1000,
    ], [
        ['line_date' => '2026-03-10', 'amount' => 1000, 'direction' => 'in', 'reference' => 'DEP-1'],
    ]);

    $session = $this->recon->start($this->company, $this->book, $this->bankAccount, $statement);
    expect($this->recon->autoMatch($session))->toBe(1);
    expect($dep->fresh()->is_cleared)->toBeTrue()->and($chq->fresh()->is_cleared)->toBeFalse();

    $session = $this->recon->complete($session->fresh());
    expect($session->status)->toBe(ReconciliationStatus::COMPLETED)
        ->and((float) $session->book_balance)->toBe(700.0)
        ->and((float) $session->outstanding_cheques)->toBe(300.0)
        ->and((float) $session->adjusted_statement_balance)->toBe(700.0)
        ->and((float) $session->difference)->toBe(0.0);

    $this->recon->assert($this->company, $this->book, $this->bankAccount);
});

it('rejects completing a reconciliation when INV-6 does not hold', function () {
    $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        500,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    ));

    $statement = $this->recon->importStatement($this->company, $this->bankAccount, [
        'period_end' => '2026-03-31',
        'closing_balance' => 999,
    ], []);

    $session = $this->recon->start($this->company, $this->book, $this->bankAccount, $statement);
    $this->recon->complete($session);
})->throws(ReconciliationException::class);

it('records a cash count and posts the over/short difference', function () {
    $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->cashAccount,
        CashTransactionType::CASH_RECEIPT,
        200,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    ));

    $count = $this->cash->recordCount($this->company, $this->book, $this->cashAccount, 180, [
        'count_date' => '2026-03-31',
        'post_difference' => true,
        'adjustment_account_code' => '620201',
    ]);

    expect($count->status)->toBe('posted')
        ->and((float) $count->difference)->toBe(-20.0)
        ->and($count->journal_id)->not->toBeNull()
        ->and((float) $this->ledger->bookBalance($this->cashAccount))->toBe(180.0);

    $this->recon->assert($this->company, $this->book, $this->cashAccount);
});

it('lists outstanding deposits and cheques', function () {
    $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        100,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    ));
    $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_PAYMENT,
        40,
        ['transaction_date' => '2026-03-11', 'counter_account_code' => '620201', 'cheque_number' => '44'],
    ));

    expect($this->ledger->outstandingDeposits($this->bankAccount))->toHaveCount(1)
        ->and($this->ledger->outstandingCheques($this->bankAccount))->toHaveCount(1)
        ->and((float) $this->ledger->outstandingDepositsTotal($this->bankAccount))->toBe(100.0)
        ->and((float) $this->ledger->outstandingChequesTotal($this->bankAccount))->toBe(40.0);
});

it('keeps posted cash transactions immutable and supports reversal', function () {
    $tx = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        50,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    ));

    expect(fn () => $this->txs->post($tx))->toThrow(PostingException::class);

    $this->txs->reverse($tx, reason: 'خطأ');
    expect($tx->fresh()->status)->toBe(DocumentStatus::REVERSED)
        ->and((float) $this->ledger->bookBalance($this->bankAccount))->toBe(0.0);
    $this->recon->assert($this->company, $this->book, $this->bankAccount);
});

it('rejects a duplicate cash transaction number', function () {
    $this->txs->createDraft($this->company, $this->book, $this->bankAccount, CashTransactionType::BANK_RECEIPT, 10, [
        'transaction_date' => '2026-03-10',
        'counter_account_code' => '410101',
        'number' => 'DUP-T',
    ]);
    $this->txs->createDraft($this->company, $this->book, $this->bankAccount, CashTransactionType::BANK_RECEIPT, 20, [
        'transaction_date' => '2026-03-11',
        'counter_account_code' => '410101',
        'number' => 'DUP-T',
    ]);
})->throws(DuplicateDocumentException::class);

it('rejects zero-amount and invalid counter accounts', function () {
    expect(fn () => $this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        0,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    ))->toThrow(PostingException::class);

    $tx = $this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        10,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '999999'],
    );
    $this->txs->post($tx);
})->throws(PostingException::class);

it('refuses posting into a hard-closed period', function () {
    FiscalPeriod::where('company_id', $this->company->id)->where('period_no', 3)->update(['status' => 'hard_closed']);
    $tx = $this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::BANK_RECEIPT,
        10,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    );
    $this->txs->post($tx);
})->throws(PostingException::class);

it('rejects linking a bank treasury account to a non-bank GL code', function () {
    $this->accounts->createAccount($this->company, [
        'code' => 'BA-BAD',
        'name_ar' => 'خاطئ',
        'type' => 'bank',
        'gl_account_code' => '620201',
    ]);
})->throws(PostingException::class);

it('writes an audit trail for treasury postings', function () {
    $tx = $this->txs->post($this->txs->createDraft(
        $this->company,
        $this->book,
        $this->bankAccount,
        CashTransactionType::MISC_RECEIPT,
        15,
        ['transaction_date' => '2026-03-10', 'counter_account_code' => '410101'],
    ));

    expect(AuditLog::where('auditable_type', $tx::class)->where('event', 'posted')->exists())->toBeTrue();
});
