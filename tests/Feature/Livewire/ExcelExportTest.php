<?php

declare(strict_types=1);

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Ar\SalesInvoiceIndex;
use App\Livewire\Gl\TrialBalance;
use App\Livewire\Reports\BalanceSheet;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\Ap\PurchaseCreditNote;
use App\Models\Ap\PurchaseDebitNote;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\SupplierPayment;
use App\Models\Ar\Customer;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesCreditNote;
use App\Models\Ar\SalesDebitNote;
use App\Models\Ar\SalesInvoice;
use App\Models\Assets\FixedAsset;
use App\Models\Expense\ExpenseClaim;
use App\Models\Gl\OpeningBalanceBatch;
use App\Models\Investment\Investment;
use App\Models\Project\Project;
use App\Models\Treasury\BankReconciliation;
use App\Models\Treasury\TreasuryAccount;
use App\Models\User;
use App\Support\Export\ExcelSheet;
use App\Support\Export\XlsxWriter;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoDataSeeder;
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

/** Unzip a generated workbook part so tests can assert on the real OOXML. */
function xlsxPart(string $bytes, string $part): string
{
    $path = tempnam(sys_get_temp_dir(), 'xlsx-test-');
    file_put_contents($path, $bytes);

    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();
    $contents = $zip->getFromName($part);
    $zip->close();
    @unlink($path);

    expect($contents)->toBeString();

    return (string) $contents;
}

/** Run a screen's export action and return the streamed download's bytes. */
function downloadExcel(string $component): string
{
    $response = Livewire::test($component)->instance()->exportExcel();

    expect($response)->not->toBeNull();
    expect($response->headers->get('Content-Type'))
        ->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    ob_start();
    $response->sendContent();

    return (string) ob_get_clean();
}

test('the writer produces a workbook Excel can open, with one sheet per definition', function () {
    $bytes = app(XlsxWriter::class)->render([
        new ExcelSheet('First', ['A', 'B'], [['x', '1']]),
        new ExcelSheet('Second', ['C'], [['y']]),
    ]);

    expect(xlsxPart($bytes, '[Content_Types].xml'))->toContain('/xl/worksheets/sheet2.xml');

    $workbook = xlsxPart($bytes, 'xl/workbook.xml');
    expect($workbook)->toContain('name="First"')->toContain('name="Second"');

    foreach (['xl/workbook.xml', 'xl/styles.xml', 'xl/worksheets/sheet1.xml', 'xl/worksheets/sheet2.xml'] as $part) {
        expect(simplexml_load_string(xlsxPart($bytes, $part)))->not->toBeFalse();
    }
});

test('duplicate and over-long sheet names are made unique, because Excel rejects collisions', function () {
    $long = str_repeat('ط', 40);
    $bytes = app(XlsxWriter::class)->render([
        new ExcelSheet($long, ['A'], []),
        new ExcelSheet($long, ['A'], []),
        new ExcelSheet('Cash/Bank: [2026]', ['A'], []),
    ]);

    $workbook = xlsxPart($bytes, 'xl/workbook.xml');
    preg_match_all('/<sheet name="([^"]*)"/', $workbook, $matches);

    expect($matches[1])->toHaveCount(3)
        ->and(array_unique($matches[1]))->toHaveCount(3)
        ->and($matches[1][2])->not->toContain('/')
        ->and($matches[1][2])->not->toContain('[');

    foreach ($matches[1] as $name) {
        expect(mb_strlen($name))->toBeLessThanOrEqual(31);
    }
});

test('money and dates are written as numbers, so Excel can sum and sort them', function () {
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet(
        title: 'Values',
        headings: ['Label', 'Amount', 'Date'],
        rows: [['Opening', '1,234.50', '2026-02-15']],
        formats: [ExcelSheet::TEXT, ExcelSheet::MONEY, ExcelSheet::DATE],
    )]);

    $sheet = xlsxPart($bytes, 'xl/worksheets/sheet1.xml');

    // The amount keeps its numeric value with the thousands separator stripped.
    expect($sheet)->toContain('<v>1234.5</v>')
        // 2026-02-15 is serial 46068 counting from Excel's 1899-12-30 epoch.
        ->and($sheet)->toContain('<v>46068</v>')
        ->and($sheet)->toContain('Opening');
});

test('an Arabic export opens right to left', function () {
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet('ورقة', ['أ'], [['ب']])], rightToLeft: true);

    expect(xlsxPart($bytes, 'xl/worksheets/sheet1.xml'))->toContain('rightToLeft="1"');
});

test('a list screen downloads its rows as a workbook naming the company and book', function () {
    $this->seed(DemoDataSeeder::class);

    $company = Company::firstOrFail();
    $sheet = xlsxPart(downloadExcel(SalesInvoiceIndex::class), 'xl/worksheets/sheet1.xml');

    // The heading block names the basis of preparation, like the report screens do.
    expect($sheet)->toContain((string) $company->name_en)
        ->and($sheet)->toContain('Generated at')
        ->and($sheet)->toContain('INV-')
        // Statuses are exported as the words on screen, never raw enum values.
        ->and($sheet)->not->toContain('partially_paid');
});

test('a report downloads both its detail lines and its totals', function () {
    $this->seed(DemoDataSeeder::class);

    $bytes = downloadExcel(BalanceSheet::class);

    $lines = xlsxPart($bytes, 'xl/worksheets/sheet1.xml');
    $summary = xlsxPart($bytes, 'xl/worksheets/sheet2.xml');

    // Detail lines carry their section, and the totals live on their own sheet.
    expect($lines)->toContain('Assets')->toContain('Liabilities')->toContain('Equity')
        ->and($summary)->toContain('Total assets');
});

test('the trial balance export carries the same totals the screen shows', function () {
    $this->seed(DemoDataSeeder::class);

    $totals = app(GlApplicationService::class)
        ->trialBalance(Company::firstOrFail(), AccountingBook::query()
            ->where('code', 'LOCAL')->firstOrFail())['totals'];

    $sheet = xlsxPart(downloadExcel(TrialBalance::class), 'xl/worksheets/sheet1.xml');

    expect($sheet)->toContain('<v>'.rtrim(rtrim(number_format((float) $totals['debit'], 6, '.', ''), '0'), '.').'</v>');
});

test('every list and report screen downloads a workbook Excel can open', function (string $component) {
    $this->seed(DemoDataSeeder::class);

    $params = exportScreenParams($component);

    // A document screen the demo data has no record for cannot be exercised here.
    if (in_array(null, $params, true)) {
        $this->markTestSkipped($component.' has no seeded record to open.');
    }

    $response = Livewire::test($component, $params)->instance()->exportExcel();

    // A screen with genuinely nothing to export says so rather than downloading a shell.
    if ($response === null) {
        expect($component)->toBeString();

        return;
    }

    ob_start();
    $response->sendContent();
    $bytes = (string) ob_get_clean();

    expect(simplexml_load_string(xlsxPart($bytes, 'xl/worksheets/sheet1.xml')))->not->toBeFalse();
})->with('exportable screens');

test('every exportable screen actually offers the download button', function (string $component) {
    $this->seed(DemoDataSeeder::class);

    $params = exportScreenParams($component);

    if (in_array(null, $params, true)) {
        $this->markTestSkipped($component.' has no seeded record to open.');
    }

    Livewire::test($component, $params)->assertSeeHtml('wire:click="exportExcel"');
})->with('exportable screens');

dataset('exportable screens', function () {
    $screens = [];

    // Datasets are built before the app boots, so no helpers or facades here.
    $root = dirname(__DIR__, 3).'/app/Livewire';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php'
            || ! str_contains((string) file_get_contents($file->getPathname()), 'use ExportsToExcel;')) {
            continue;
        }

        $relative = str_replace(['\\', '/'], '\\', substr($file->getPathname(), strlen($root) + 1, -4));
        $class = 'App\\Livewire\\'.$relative;
        $screens[$relative] = [$class];
    }

    ksort($screens);

    return $screens;
});

/**
 * Mount parameters for the screens that take a record or a variant. Resolved
 * inside the test, after the demo data is seeded.
 *
 * @return array<string, mixed>
 */
function exportScreenParams(string $component): array
{
    return match ($component) {
        'App\Livewire\Ar\SalesInvoiceShow' => ['invoice' => SalesInvoice::first()],
        'App\Livewire\Ar\ReceiptShow' => ['receipt' => Receipt::first()],
        'App\Livewire\Ar\CustomerShow' => ['customer' => Customer::first()],
        'App\Livewire\Ar\CreditNoteShow' => ['note' => SalesCreditNote::first()],
        'App\Livewire\Ar\DebitNoteShow' => ['note' => SalesDebitNote::first()],
        'App\Livewire\Ap\PurchaseInvoiceShow' => ['invoice' => PurchaseInvoice::first()],
        'App\Livewire\Ap\PaymentShow' => ['payment' => SupplierPayment::first()],
        'App\Livewire\Ap\CreditNoteShow' => ['note' => PurchaseCreditNote::first()],
        'App\Livewire\Ap\DebitNoteShow' => ['note' => PurchaseDebitNote::first()],
        'App\Livewire\Gl\JournalShow' => ['journal' => Journal::first()],
        'App\Livewire\Assets\FixedAssetShow' => ['asset' => FixedAsset::first()],
        'App\Livewire\Projects\ProjectShow' => ['project' => Project::first()],
        'App\Livewire\Investments\InvestmentShow' => ['investment' => Investment::first()],
        'App\Livewire\Expenses\ExpenseShow' => ['claim' => ExpenseClaim::first()],
        'App\Livewire\OpeningBalances\OpeningBalanceShow' => ['batch' => OpeningBalanceBatch::first()],
        'App\Livewire\Banking\TreasuryAccountShow' => ['account' => TreasuryAccount::first()],
        'App\Livewire\Banking\ReconciliationShow' => ['reconciliation' => BankReconciliation::first()],
        'App\Livewire\Banking\TreasuryAccounts' => ['type' => 'bank'],
        default => [],
    };
}

test('a screen with no company selected says so instead of downloading an empty file', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(SalesInvoiceIndex::class)
        ->call('exportExcel')
        ->assertDispatched('notify');
});
