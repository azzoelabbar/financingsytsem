<?php

declare(strict_types=1);

use App\Enums\Accounting\PeriodStatus;
use App\Livewire\Gl\PeriodIndex;
use App\Livewire\OpeningBalances\OpeningBalanceCreate;
use App\Livewire\OpeningBalances\OpeningBalanceShow;
use App\Livewire\Tax\TaxCodeCreate;
use App\Livewire\Tax\TaxRateCreate;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Gl\OpeningBalanceBatch;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxRate;
use App\Models\User;
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

test('tax code and legally referenced rate are configured from livewire', function () {
    Livewire::test(TaxCodeCreate::class)->set('code', 'VAT-WEB')->set('name', 'Website VAT')->set('kind', 'output_vat')->set('gl_account_code', '210404')->call('save')->assertHasNoErrors();
    $code = TaxCode::query()->where('code', 'VAT-WEB')->firstOrFail();
    Livewire::test(TaxRateCreate::class)->set('tax_code_id', $code->id)->set('rate', '15')->set('effective_from', '2026-01-01')->set('legal_reference', 'TEST-LAW-2026')->call('save')->assertHasNoErrors();
    $rate = TaxRate::query()->where('tax_code_id', $code->id)->firstOrFail();
    expect($rate->rate)->toBe('15.000000')->and($rate->effective_to)->toBeNull();
});

test('opening batch is built validated posted and locked from its workspace', function () {
    Livewire::test(OpeningBalanceCreate::class)->set('as_of', '2026-01-01')->set('currency', 'LYD')->call('save')->assertHasNoErrors();
    $batch = OpeningBalanceBatch::firstOrFail();
    Livewire::test(OpeningBalanceShow::class, ['batch' => $batch])->set('accountCode', '110101')->set('side', 'debit')->set('amount', '1000')->call('addLine')->assertHasNoErrors();
    Livewire::test(OpeningBalanceShow::class, ['batch' => $batch->fresh()])->set('accountCode', '310101')->set('side', 'credit')->set('amount', '1000')->call('addLine')->assertHasNoErrors();
    Livewire::test(OpeningBalanceShow::class, ['batch' => $batch->fresh()])->call('validateBatch')->assertHasNoErrors();
    Livewire::test(OpeningBalanceShow::class, ['batch' => $batch->fresh()])->call('postBatch')->assertHasNoErrors();
    Livewire::test(OpeningBalanceShow::class, ['batch' => $batch->fresh()])->call('lockBatch')->assertHasNoErrors();
    expect($batch->fresh()->status)->toBe('locked')->and($batch->fresh()->journal_id)->not->toBeNull()->and($batch->lines()->count())->toBe(2);
});

test('temporary account is rejected in opening batch', function () {
    Livewire::test(OpeningBalanceCreate::class)->set('as_of', '2026-01-01')->call('save');
    $batch = OpeningBalanceBatch::firstOrFail();
    Livewire::test(OpeningBalanceShow::class, ['batch' => $batch])->set('accountCode', '410101')->set('side', 'credit')->set('amount', '100')->call('addLine')->assertHasErrors('action');
    expect($batch->lines()->count())->toBe(0);
});

test('period lifecycle transitions and locked state is terminal', function () {
    $period = FiscalPeriod::query()->where('period_no', 1)->firstOrFail();
    Livewire::test(PeriodIndex::class)->call('changeStatus', $period->id, 'soft_closed')->assertHasNoErrors();
    Livewire::test(PeriodIndex::class)->call('changeStatus', $period->id, 'hard_closed')->assertHasNoErrors();
    Livewire::test(PeriodIndex::class)->call('changeStatus', $period->id, 'locked')->assertHasNoErrors();
    Livewire::test(PeriodIndex::class)->set('reopenReason', 'Test reopening')->call('changeStatus', $period->id, 'open')->assertHasErrors('period');
    expect($period->fresh()->status)->toBe(PeriodStatus::LOCKED);
});
