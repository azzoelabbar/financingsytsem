<?php

declare(strict_types=1);

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\MultiBookService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\ReportingPackService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Expense\ExpenseClaimService;
use App\Services\Expense\ExpenseReimbursementService;
use App\Services\Gl\OpeningBalanceService;
use App\Services\Investment\InvestmentIncomeService;
use App\Services\Investment\InvestmentService;
use App\Services\Localization\CountryPackService;
use App\Services\Localization\Exceptions\RuleNotFoundException;
use App\Services\Localization\LegalInvoiceNumberService;
use App\Services\Project\ProjectReportingService;
use App\Services\Project\ProjectService;
use App\Services\Tax\TaxResolver;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->ifrs = AccountingBook::where('company_id', $this->company->id)->where('code', 'IFRS')->firstOrFail();
    $this->taxBook = AccountingBook::where('company_id', $this->company->id)->where('code', 'TAX')->firstOrFail();
    $this->roles = app(AccountRoleResolver::class);
});

it('covers FVTPL gain loss dividend disposal and rejects invalid inputs', function () {
    $svc = app(InvestmentService::class);
    $income = app(InvestmentIncomeService::class);

    expect(fn () => $svc->acquire($this->company, $this->book, [
        'code' => 'BAD', 'classification' => 'nonsense', 'cost' => 10, 'date' => '2026-03-01',
    ]))->toThrow(PostingException::class);
    expect(fn () => $svc->acquire($this->company, $this->book, [
        'code' => 'Z', 'classification' => 'FVTPL', 'cost' => 0, 'date' => '2026-03-01',
    ]))->toThrow(PostingException::class);
    expect(fn () => $svc->acquire($this->company, $this->book, [
        'code' => 'Q', 'classification' => 'FVTPL', 'quantity' => 0, 'unit_cost' => 10, 'date' => '2026-03-01',
    ]))->toThrow(PostingException::class);

    $gain = $svc->acquire($this->company, $this->book, [
        'code' => 'FVTPL-G', 'classification' => 'FVTPL', 'cost' => 1000, 'date' => '2026-03-01',
        'dimensions' => ['BRANCH' => 'HQ'],
    ]);
    expect($gain->journal_id)->not->toBeNull()->and($gain->journal->isBalanced())->toBeTrue();
    $svc->revalue($gain, '2026-03-10', 1100);
    expect((float) $gain->fresh()->carrying_amount)->toBe(1100.0);

    $loss = $svc->acquire($this->company, $this->book, [
        'code' => 'FVTPL-L', 'classification' => 'fvtpl', 'cost' => 500, 'date' => '2026-03-01',
    ]);
    $svc->revalue($loss, '2026-03-11', 450);
    $income->dividend($loss->fresh(), '2026-03-12', 20);
    $svc->dispose($loss->fresh(), '2026-03-31', 450);
    expect($loss->fresh()->status)->toBe('disposed');
    expect(app(IntegrityService::class)->check($this->company, $this->book)['passed'])->toBeTrue();
});

it('covers FVOCI and amortized-cost interest accrual', function () {
    $svc = app(InvestmentService::class);
    $income = app(InvestmentIncomeService::class);

    $fvoci = $svc->acquire($this->company, $this->book, [
        'code' => 'FVOCI-1', 'classification' => 'FVOCI', 'cost' => 800, 'date' => '2026-03-01',
    ]);
    $svc->revalue($fvoci, '2026-03-15', 860);
    $svc->revalue($fvoci->fresh(), '2026-03-20', 820);
    expect((float) $fvoci->fresh()->carrying_amount)->toBe(820.0);

    $amort = $svc->acquire($this->company, $this->book, [
        'code' => 'AC-1',
        'classification' => 'AMORTIZED_COST',
        'cost' => 1200,
        'date' => '2026-03-01',
        'effective_interest_rate' => 12,
    ]);
    expect(fn () => $svc->revalue($amort, '2026-03-31', 1300))->toThrow(PostingException::class);
    $income->accrueInterest($amort, '2026-03-31');
    expect((float) $amort->fresh()->carrying_amount)->toBeGreaterThan(1200.0);
});

it('runs expense draft submit approve post partial reimbursement and reject path', function () {
    $claims = app(ExpenseClaimService::class);
    $pay = app(ExpenseReimbursementService::class);

    $draft = $claims->createDraft($this->company, $this->book, [
        'number' => 'EXP-10',
        'claim_date' => '2026-03-10',
        'employee_ref' => 'E1',
        'dimensions' => ['COST_CENTER' => 'ADM'],
    ], [
        ['expense_account' => '620201', 'amount' => 100, 'description' => 'Travel'],
    ]);
    expect($draft->status)->toBe('draft')->and($draft->journal_id)->toBeNull();

    $claims->submit($draft);
    $rejected = $claims->createDraft($this->company, $this->book, [
        'number' => 'EXP-11', 'claim_date' => '2026-03-10',
    ], [['amount' => 40]]);
    $claims->submit($rejected);
    $claims->reject($rejected->fresh());
    expect(fn () => $claims->post($rejected->fresh()))->toThrow(PostingException::class);

    expect(fn () => $claims->post($draft->fresh()))->toThrow(PostingException::class);
    $claims->approve($draft->fresh());
    $posted = $claims->post($draft->fresh());
    expect($posted->journal->isBalanced())->toBeTrue()
        ->and($posted->payable_account_code)->toBe($this->roles->code($this->company, 'expense.employee_payable'));

    $pay->reimburse($posted, '2026-03-12', 40);
    expect((float) $posted->fresh()->reimbursed_amount)->toBe(40.0)->and($posted->fresh()->status)->toBe('posted');
    $pay->reimburse($posted->fresh(), '2026-03-13');
    expect($posted->fresh()->status)->toBe('reimbursed');

    expect(fn () => $claims->createDraft($this->company, $this->book, [
        'number' => 'EXP-10', 'claim_date' => '2026-03-10',
    ], [['amount' => 10]]))->toThrow(PostingException::class);
});

it('charges capitalizes projects with budget variance and rejects over-capitalization', function () {
    $svc = app(ProjectService::class);
    $project = $svc->define($this->company, ['code' => 'PRJ-1', 'name' => 'Plant', 'budget' => 1000]);
    $svc->addMilestone($project, 'Foundation', '2026-04-01');
    $svc->charge($project, '2026-03-05', 400);
    $svc->charge($project->fresh(), '2026-03-06', 200);
    $svc->capitalize($project->fresh(), '2026-03-20', 250);
    $summary = app(ProjectReportingService::class)->summary($project->fresh());
    expect((float) $summary['charged'])->toBe(600.0)
        ->and((float) $summary['capitalized'])->toBe(250.0)
        ->and((float) $summary['uncapitalized'])->toBe(350.0)
        ->and((float) $summary['variance'])->toBe(400.0)
        ->and($summary['costs'][0]['journal_id'])->not->toBeNull();
    expect(fn () => $svc->capitalize($project->fresh(), '2026-03-21', 9999))->toThrow(PostingException::class);
});

it('issues legal invoice numbers and builds GL-derived VAT return from localization', function () {
    $pack = app(CountryPackService::class);
    $pack->publishRule([
        'country' => 'LY', 'rule_code' => 'LY_VAT_STANDARD', 'rule_type' => 'tax_rate',
        'name_ar' => 'VAT', 'authority' => 'TEST', 'legal_reference' => 'LY-VAT-LAW',
        'rate' => '2', 'effective_from' => '2026-01-01', 'status' => 'active',
    ]);
    $pack->publishRule([
        'country' => 'LY', 'rule_code' => 'LY_INVOICE_NUMBERING', 'rule_type' => 'numbering_rule',
        'name_ar' => 'Num', 'authority' => 'TEST', 'legal_reference' => 'LY-NUM',
        'effective_from' => '2026-01-01', 'status' => 'active', 'meta' => ['prefix' => 'LY-INV-'],
    ]);

    $legal = app(LegalInvoiceNumberService::class);
    $a = $legal->issue($this->company, 'LY', 'LY_INVOICE_NUMBERING', '2026-03-01');
    $b = $legal->issue($this->company, 'LY', 'LY_INVOICE_NUMBERING', '2026-03-01');
    expect($a->number)->toBe('LY-INV-00001')->and($b->number)->toBe('LY-INV-00002')
        ->and($a->legal_reference)->toBe('LY-NUM');

    $resolved = app(TaxResolver::class)->compute($this->company, 'LY_VAT_STANDARD', 1000, '2026-03-10', 'LY');
    expect((float) $resolved['tax'])->toBe(20.0)->and($resolved['legal_reference'])->toBe('LY-VAT-LAW');

    app(EnginePoster::class)->post($this->company, 'tax.adjustment', '2026-03-10', 'LYD', 'VAT-OUT', [
        ['account' => $this->roles->code($this->company, 'bank'), 'debit' => 50],
        ['account' => $this->roles->code($this->company, 'tax.output_vat'), 'credit' => 50],
    ]);
    $ret = $pack->vatReturn($this->company, $this->book, 'LY', Carbon::parse('2026-03-31'));
    expect((float) $ret['output'])->toBe(50.0)->and($ret['legal_reference'])->toBe('LY-VAT-LAW');
    expect(fn () => $pack->vatReturn($this->company, $this->book, 'EG', Carbon::parse('2026-03-31')))
        ->toThrow(RuleNotFoundException::class);
});

it('posts opening balance batches and rejects P&L or unbalanced or duplicate', function () {
    $ob = app(OpeningBalanceService::class);
    $batch = $ob->createDraft($this->company, $this->book, '2026-01-01');
    $ob->addLine($batch, ['account' => '110102', 'debit' => 5000]);
    $ob->addLine($batch, ['account' => '310101', 'credit' => 5000]);
    $posted = $ob->postBatch($batch->fresh());
    expect($posted->status)->toBe('posted')->and($posted->journal->isBalanced())->toBeTrue();
    $ob->lock($posted->fresh());
    expect(fn () => $ob->postBatch($posted->fresh()))->toThrow(PostingException::class);

    expect(fn () => $ob->createDraft($this->company, $this->book, '2026-01-01'))->toThrow(PostingException::class);

    $bad = $ob->createDraft($this->company, $this->book, '2026-01-02');
    expect(fn () => $ob->addLine($bad, ['account' => '410101', 'credit' => 10]))->toThrow(PostingException::class);

    $unbal = $ob->createDraft($this->company, $this->book, '2026-01-03');
    $ob->addLine($unbal, ['account' => '110102', 'debit' => 10]);
    $ob->addLine($unbal, ['account' => '310101', 'credit' => 9]);
    expect(fn () => $ob->validate($unbal->fresh()))->toThrow(PostingException::class);
});

it('posts book-specific mappings across LOCAL IFRS TAX and isolates books', function () {
    $multi = app(MultiBookService::class);
    $multi->mapAccount($this->company, $this->ifrs, '620201', '620203');
    $multi->setPostingRule($this->company, $this->taxBook, 'cost.allocate', '0.5');

    $journals = $multi->postParallel($this->company, 'cost.allocate', '2026-03-10', 'LYD', 'MB-X', [
        ['account' => '620201', 'debit' => 100, 'memo' => 'exp'],
        ['account' => '110102', 'credit' => 100, 'memo' => 'bank'],
    ], [BookBasis::LOCAL, BookBasis::IFRS, BookBasis::TAX]);

    expect($journals)->toHaveCount(3);
    $local = $journals[0]->load('lines');
    $ifrs = $journals[1]->load('lines');
    $tax = $journals[2]->load('lines');
    expect($local->book_id)->toBe($this->book->id)
        ->and($ifrs->book_id)->toBe($this->ifrs->id)
        ->and($tax->book_id)->toBe($this->taxBook->id);

    $ifrsExpense = $ifrs->lines->first(fn ($l) => (float) $l->debit > 0);
    expect($ifrsExpense->account_id)->toBe(Account::where('company_id', $this->company->id)->where('code', '620203')->value('id'));
    expect((float) $tax->lines->first(fn ($l) => (float) $l->debit > 0)->debit)->toBe(50.0);

    $this->ifrs->forceFill(['is_active' => false])->save();
    expect(fn () => $multi->postParallel($this->company, 'cost.allocate', '2026-03-11', 'LYD', 'MB-Y', [
        ['account' => '620201', 'debit' => 10],
        ['account' => '110102', 'credit' => 10],
    ], [BookBasis::IFRS]))->toThrow(PostingException::class);
    $this->ifrs->forceFill(['is_active' => true])->save();

    $localTb = app(TrialBalanceService::class)->build($this->company, $this->book);
    $ifrsTb = app(TrialBalanceService::class)->build($this->company, $this->ifrs);
    expect($localTb->firstWhere('code', '620201'))->not->toBeNull();
    expect($ifrsTb->firstWhere('code', '620201'))->toBeNull();
});

it('builds management pack with OCI cash flow forecasts and book context', function () {
    $inv = app(InvestmentService::class)->acquire($this->company, $this->book, [
        'code' => 'OCI-R', 'classification' => 'FVOCI', 'cost' => 300, 'date' => '2026-03-01',
    ]);
    app(InvestmentService::class)->revalue($inv, '2026-03-31', 375);

    $pack = app(ReportingPackService::class);
    $mgmt = $pack->managementPack($this->company, $this->book, new ReportRequest(
        companyId: $this->company->id,
        bookId: $this->book->id,
    ));
    expect($mgmt['balance_sheet']['balanced'])->toBeTrue()
        ->and($mgmt['cash_flow'])->toHaveKeys(['operating', 'investing', 'financing', 'lines'])
        ->and($mgmt)->toHaveKeys(['oci', 'ar_aging', 'ap_aging', 'cash_forecast', 'investment_summary', 'tax_summary', 'project_costs', 'budget_vs_actual'])
        ->and($mgmt['book_basis'])->toBe('local')
        ->and((float) $mgmt['oci']['total'])->toBe(75.0)
        ->and($mgmt['oci']['lines'][0]['journal_ids'])->not->toBeEmpty();
});

it('rejects investment posting into a hard-closed period', function () {
    $period = FiscalPeriod::where('company_id', $this->company->id)->where('period_no', 3)->firstOrFail();
    app(PeriodService::class)->hardClose($period);
    expect(fn () => app(InvestmentService::class)->acquire($this->company, $this->book, [
        'code' => 'CLOSED', 'classification' => 'FVTPL', 'cost' => 10, 'date' => '2026-03-15',
    ]))->toThrow(PostingException::class);
});
