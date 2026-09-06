<?php

declare(strict_types=1);

use App\Livewire\Banking\ReconciliationCreate;
use App\Livewire\Banking\ReconciliationShow;
use App\Livewire\Banking\TransactionCreate;
use App\Livewire\Banking\TransactionShow;
use App\Livewire\Banking\TreasuryAccountCreate;
use App\Models\Treasury\BankReconciliation;
use App\Models\Treasury\CashTransaction;
use App\Models\Treasury\TreasuryAccount;
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
    $this->actingAs(User::where('email', 'test@example.com')->firstOrFail());
});

test('bank and cash accounts are created through livewire', function () {
    Livewire::test(TreasuryAccountCreate::class, ['type' => 'bank'])->set('code', 'BANK-UAT')->set('name_ar', 'بنك اختبار')->set('name_en', 'Test Bank Account')->set('bank_code', 'BANK-MASTER')->set('bank_name_ar', 'مصرف اختبار')->set('bank_name_en', 'Test Bank')->set('gl_account_code', '110102')->call('save')->assertHasNoErrors();
    Livewire::test(TreasuryAccountCreate::class, ['type' => 'cash'])->set('code', 'CASH-UAT')->set('name_ar', 'صندوق اختبار')->set('name_en', 'Test Cash')->set('gl_account_code', '110101')->call('save')->assertHasNoErrors();
    expect(TreasuryAccount::count())->toBe(2)->and(TreasuryAccount::where('code', 'BANK-UAT')->first()->bank_id)->not->toBeNull();
});

test('treasury transaction posts and reconciles from website components', function () {
    Livewire::test(TreasuryAccountCreate::class, ['type' => 'bank'])->set('code', 'BANK-FLOW')->set('name_ar', 'حساب بنك')->set('name_en', 'Flow Bank')->set('bank_code', 'FLOW')->set('bank_name_ar', 'مصرف التدفق')->set('gl_account_code', '110102')->call('save');
    $account = TreasuryAccount::where('code', 'BANK-FLOW')->firstOrFail();
    Livewire::test(TransactionCreate::class)->set('treasury_account_id', $account->id)->set('type', 'bank_receipt')->set('transaction_date', '2026-05-01')->set('amount', '100')->set('currency', 'LYD')->set('counter_account_code', '410101')->set('reference', 'BANK-REF-1')->call('save')->assertHasNoErrors();
    $tx = CashTransaction::where('reference', 'BANK-REF-1')->firstOrFail();
    Livewire::test(TransactionShow::class, ['transaction' => $tx])->call('post')->assertHasNoErrors();
    Livewire::test(ReconciliationCreate::class)->set('treasury_account_id', $account->id)->set('statement_number', 'STMT-1')->set('statement_date', '2026-05-01')->set('period_start', '2026-05-01')->set('period_end', '2026-05-01')->set('opening_balance', '0')->set('closing_balance', '100')->set('lines.0.line_date', '2026-05-01')->set('lines.0.reference', 'BANK-REF-1')->set('lines.0.description', 'Bank receipt')->set('lines.0.amount', '100')->set('lines.0.direction', 'in')->call('save')->assertHasNoErrors();
    $recon = BankReconciliation::firstOrFail();
    Livewire::test(ReconciliationShow::class, ['reconciliation' => $recon])->call('complete')->assertHasNoErrors();
    expect($tx->fresh()->journal_id)->not->toBeNull()->and($tx->fresh()->is_cleared)->toBeTrue()->and($recon->fresh()->status->value)->toBe('completed')->and($recon->fresh()->difference)->toBe('0.000000');
});
