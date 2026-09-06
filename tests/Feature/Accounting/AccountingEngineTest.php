<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Data\JournalDraft;
use App\Services\Accounting\Data\LedgerMovement;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Engine\RuleRegistry;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Rules\SalesInvoiceRule;
use App\Services\Accounting\TrialBalanceService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->company = Company::firstOrFail();

    $registry = new RuleRegistry;
    $registry->register(new SalesInvoiceRule);
    $this->engine = new AccountingEngine(app(JournalService::class), $registry);

    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();
});

it('posts a sales invoice through the engine from a source document', function () {
    $document = new GenericSourceDocument(
        type: 'sales.invoice',
        date: '2026-03-10',
        currency: 'LYD',
        reference: 'INV-1001',
        payload: ['net' => '1000', 'vat' => '50'],
    );

    $journal = $this->engine->postFrom($this->company, $document);

    expect($journal->status)->toBe(JournalStatus::POSTED)
        ->and($journal->source)->toBe('sales')
        ->and($journal->reference)->toBe('INV-1001')
        ->and($journal->lines)->toHaveCount(3)
        ->and($journal->isBalanced())->toBeTrue();

    $line = fn (string $code) => $journal->lines->firstWhere('account_id', ($this->acc)($code)->id);
    expect((float) $line('110201')->debit)->toBe(1050.0)   // AR gross
        ->and((float) $line('410101')->credit)->toBe(1000.0) // revenue net
        ->and((float) $line('210401')->credit)->toBe(50.0);  // output VAT

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->company->primaryBook());
    expect($tb['balanced'])->toBeTrue();
});

it('omits the tax line when there is no VAT', function () {
    $document = new GenericSourceDocument(
        type: 'sales.invoice',
        date: '2026-03-10',
        currency: 'LYD',
        reference: 'INV-1002',
        payload: ['net' => '500'],
    );

    $journal = $this->engine->postFrom($this->company, $document);

    expect($journal->lines)->toHaveCount(2)
        ->and($journal->isBalanced())->toBeTrue();
});

it('rejects a source document with no registered rule', function () {
    $document = new GenericSourceDocument('unknown.type', '2026-03-10', 'LYD', null, []);

    $this->engine->postFrom($this->company, $document);
})->throws(PostingException::class);

it('rejects a draft that references a missing account', function () {
    $draft = new JournalDraft(
        book: 'local',
        source: 'manual',
        date: '2026-03-10',
        reference: null,
        description: 'bad draft',
        movements: [
            LedgerMovement::debit('999999', '100'),
            LedgerMovement::credit('410101', '100'),
        ],
        ruleVersion: 'test',
    );

    $this->engine->post($this->company, $draft);
})->throws(PostingException::class);

it('builds a balanced draft directly from a rule (unit-style)', function () {
    $document = new GenericSourceDocument('sales.invoice', '2026-03-10', 'LYD', 'INV-1', ['net' => '800', 'vat' => '40']);

    $draft = (new SalesInvoiceRule)->build($document);

    expect($draft->isBalanced())->toBeTrue()
        ->and($draft->book)->toBe('local')
        ->and($draft->ruleVersion)->toBe('1.1') // rule evolved to data-driven lines (spec §49 versioning)
        ->and((float) $draft->totalDebit())->toBe(840.0);
});
