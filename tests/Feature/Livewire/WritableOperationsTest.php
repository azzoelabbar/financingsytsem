<?php

declare(strict_types=1);

use App\Livewire\Assets\FixedAssetCreate;
use App\Livewire\Assets\FixedAssetShow;
use App\Livewire\Expenses\ExpenseCreate;
use App\Livewire\Expenses\ExpenseShow;
use App\Livewire\Investments\InvestmentCreate;
use App\Livewire\Investments\InvestmentShow;
use App\Livewire\Projects\ProjectCreate;
use App\Livewire\Projects\ProjectShow;
use App\Livewire\Reports\BudgetVsActual;
use App\Models\Accounting\Journal;
use App\Models\Assets\FixedAsset;
use App\Models\Expense\ExpenseClaim;
use App\Models\Investment\Investment;
use App\Models\Project\Project;
use App\Models\User;
use App\Services\Ap\ApReconciliationService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());
});

test('expense claim completes the website lifecycle', function () {
    Livewire::test(ExpenseCreate::class)->set('number', 'EXP-WEB-1')->set('employee_ref', 'Employee One')
        ->set('claim_date', '2026-06-01')->set('expense_account', '620101')->set('amount', '120')->call('save')->assertHasNoErrors();
    $claim = ExpenseClaim::query()->where('number', 'EXP-WEB-1')->firstOrFail();
    Livewire::test(ExpenseShow::class, ['claim' => $claim])->call('submit')->assertHasNoErrors();
    Livewire::test(ExpenseShow::class, ['claim' => $claim->fresh()])->call('approve')->assertHasNoErrors();
    Livewire::test(ExpenseShow::class, ['claim' => $claim->fresh()])->call('post')->assertHasNoErrors();
    Livewire::test(ExpenseShow::class, ['claim' => $claim->fresh()])->set('reimbursementDate', '2026-06-02')->set('reimbursementAmount', '50')->call('reimburse')->assertHasNoErrors();
    Livewire::test(ExpenseShow::class, ['claim' => $claim->fresh()])->set('reimbursementDate', '2026-06-03')->set('reimbursementAmount', '70')->call('reimburse')->assertHasNoErrors();
    expect($claim->fresh()->status)->toBe('reimbursed')
        ->and($claim->fresh()->reimbursed_amount)->toBe('120.000000')
        ->and($claim->fresh()->journal_id)->not->toBeNull()
        ->and($claim->reimbursements()->whereNull('journal_id')->count())->toBe(0);
});

test('project costs and capitalization are posted from workspace', function () {
    Livewire::test(ProjectCreate::class)->set('code', 'PRJ-WEB-1')->set('name', 'Website Project')->set('start_date', '2026-06-01')->set('budget', '1000')->call('save')->assertHasNoErrors();
    $project = Project::query()->where('code', 'PRJ-WEB-1')->firstOrFail();
    Livewire::test(ProjectShow::class, ['project' => $project])->set('actionDate', '2026-06-03')->set('costAmount', '300')->call('charge')->assertHasNoErrors();
    Livewire::test(ProjectShow::class, ['project' => $project->fresh()])->set('actionDate', '2026-06-04')->set('capitalizationAmount', '150')->call('capitalize')->assertHasNoErrors();
    expect($project->fresh()->charged)->toBe('300.000000')->and($project->fresh()->capitalized)->toBe('150.000000')->and($project->costs()->first()->journal_id)->not->toBeNull();
});

test('budget lines are writable from the budget versus actual report', function () {
    Livewire::test(BudgetVsActual::class)
        ->set('budgetPeriod', '2026-06')
        ->set('budgetAccount', '620101')
        ->set('budgetAmount', '500')
        ->call('saveBudget')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('budget_lines', [
        'period_key' => '2026-06',
        'account_code' => '620101',
        'amount' => 500,
    ]);
});

test('investment acquisition revaluation income and disposal are writable', function () {
    Livewire::test(InvestmentCreate::class)->set('code', 'INV-WEB-1')->set('name', 'Website Investment')->set('classification', 'FVTPL')->set('date', '2026-06-01')->set('quantity', '10')->set('cost', '1000')->call('save')->assertHasNoErrors();
    $investment = Investment::query()->where('code', 'INV-WEB-1')->firstOrFail();
    Livewire::test(InvestmentShow::class, ['investment' => $investment])->set('actionDate', '2026-06-02')->set('fairValue', '1100')->call('revalue')->assertHasNoErrors();
    Livewire::test(InvestmentShow::class, ['investment' => $investment->fresh()])->set('actionDate', '2026-06-03')->set('incomeAmount', '25')->call('recordIncome')->assertHasNoErrors();
    Livewire::test(InvestmentShow::class, ['investment' => $investment->fresh()])->set('actionDate', '2026-06-04')->set('proceeds', '1120')->call('dispose')->assertHasNoErrors();
    expect($investment->fresh()->status)->toBe('disposed')
        ->and($investment->transactions()->count())->toBe(4)
        ->and($investment->transactions()->whereNull('journal_id')->count())->toBe(0)
        ->and($investment->disposals()->count())->toBe(1);
});

test('fixed asset acquisition depreciation and disposal are writable', function () {
    Livewire::test(FixedAssetCreate::class)->set('code', 'FA-WEB-CONTROL')->set('name', 'Invalid Control Asset')->set('cost', '100')->set('useful_life_months', 12)->set('in_service_date', '2026-06-01')->set('ap_account_code', '210101')->call('save')->assertHasErrors(['ap_account_code']);
    Livewire::test(FixedAssetCreate::class)->assertSet('ap_account_code', '210103')->set('code', 'FA-WEB-1')->set('name', 'Website Asset')->set('cost', '1200')->set('useful_life_months', 12)->set('in_service_date', '2026-06-01')->call('save')->assertHasNoErrors();
    $asset = FixedAsset::query()->where('code', 'FA-WEB-1')->firstOrFail();
    $apCheck = app(ApReconciliationService::class)->reconcile($asset->company, $asset->book);
    expect($apCheck->passed())->toBeTrue();
    Livewire::test(FixedAssetShow::class, ['asset' => $asset])->set('actionDate', '2026-06-30')->call('depreciate')->assertHasNoErrors();
    expect($asset->fresh()->accum_depreciation)->toBe('100.000000');
    Livewire::test(FixedAssetShow::class, ['asset' => $asset->fresh()])->set('actionDate', '2026-07-01')->set('disposalProceeds', '1150')->call('dispose')->assertHasNoErrors();
    expect($asset->fresh()->status)->toBe('disposed')
        ->and($asset->fresh()->accum_depreciation)->toBe('100.000000')
        ->and($asset->fresh()->netBookValue())->toBe('0.000000')
        ->and(Journal::query()->where('company_id', $asset->company_id)->where('reference', 'FA-WEB-1')->count())->toBe(3);
});
