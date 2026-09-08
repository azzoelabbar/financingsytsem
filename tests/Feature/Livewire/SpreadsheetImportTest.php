<?php

declare(strict_types=1);

use App\Livewire\Imports\ImportWorkspace;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\SupplierPayment;
use App\Models\Ar\Customer;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesInvoice;
use App\Models\Expense\ExpenseClaim;
use App\Models\Inventory\InventoryItem;
use App\Models\Security\AccessGrant;
use App\Models\SpreadsheetImport;
use App\Models\User;
use App\Services\Ap\SupplierService;
use App\Services\Ar\CustomerService;
use App\Support\Export\ExcelSheet;
use App\Support\Export\XlsxWriter;
use App\Support\Import\ImportCatalog;
use App\Support\Import\SpreadsheetImporter;
use App\Support\Import\XlsxReader;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([AccountingReferenceSeeder::class, DemoCompanySeeder::class, DemoUserSeeder::class]);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());
});

function importFile(string $kind, array $rows): UploadedFile
{
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet('Sheet1', ImportCatalog::schemas()[$kind], $rows)]);

    return UploadedFile::fake()->createWithContent('uat-'.$kind.'.xlsx', $bytes);
}

function importParty(): void
{
    $company = Company::firstOrFail();
    app(CustomerService::class)->create($company, ['code' => 'IMP-C', 'name_ar' => 'Import Customer']);
    app(SupplierService::class)->create($company, ['code' => 'IMP-S', 'name_ar' => 'Import Supplier']);
}

test('customer workbook preview needs confirmation and preserves phone without opening journals', function () {
    $journals = DB::table('journals')->count();
    $component = Livewire::test(ImportWorkspace::class, ['kind' => 'customers'])
        ->set('file', importFile('customers', [['IMP-1', 'عميل استيراد', '0910000000', '1200']]))
        ->call('preview')->assertHasNoErrors()->assertSet('valid', true);
    expect(Customer::where('code', 'IMP-1')->exists())->toBeFalse();
    $component->call('confirm')->assertHasErrors('acknowledged')
        ->set('acknowledged', true)->call('confirm')->assertHasNoErrors();
    $customer = Customer::where('code', 'IMP-1')->firstOrFail();
    expect($customer->contacts()->first()->phone)->toBe('0910000000')
        ->and(DB::table('journals')->count())->toBe($journals)
        ->and(SpreadsheetImport::first()->payload['rows'][0]['cells'][3])->toBe('1200');
    Livewire::test(ImportWorkspace::class, ['kind' => 'customers'])->set('file', importFile('customers', [['IMP-1', 'Duplicate', '', '0']]))
        ->call('preview')->assertHasErrors('import')->assertSet('valid', false);
});

test('all supported document imports create only drafts through services', function (string $kind, array $rows, string $model, string $total) {
    importParty();
    $journals = DB::table('journals')->count();
    Livewire::test(ImportWorkspace::class, ['kind' => $kind])
        ->set('options.account', $kind === 'sales' ? '410101' : '510101')
        ->set('options.cash_account', '110101')->set('options.bank_account', '110102')->set('options.employee_ref', 'UAT')
        ->set('file', importFile($kind, $rows))->call('preview')->assertHasNoErrors()->assertSet('documentCount', 1)
        ->set('acknowledged', true)->call('confirm')->assertHasNoErrors();
    $document = $model::latest('id')->firstOrFail();
    expect($document->status instanceof BackedEnum ? $document->status->value : $document->status)->toBe('draft')->and($document->journal_id)->toBeNull()
        ->and(DB::table('journals')->count())->toBe($journals);
    if (in_array($kind, ['sales', 'purchases'])) {
        expect($document->gross_total)->toBe($total)->and($document->lines()->count())->toBe(2);
    }
})->with([
    ['sales', [['I1', '1-4-2026', 'IMP-C', 'Customer', 'P1', 'Line one', '2', '100', '200', 'نقدي', '300', '0', '0', '0', '0'], ['I1', '1-4-2026', 'IMP-C', 'Customer', 'P2', 'Line two', '1', '100', '100', '', '', '', '', '', '']], SalesInvoice::class, '300.000000'],
    ['purchases', [['P1', '1-4-2026', 'IMP-S', 'Supplier', 'P1', 'Line one', '2', '100', '200', 'نقدي', '300', '0'], ['P1', '1-4-2026', 'IMP-S', 'Supplier', 'P2', 'Line two', '1', '100', '100', '', '', '']], PurchaseInvoice::class, '300.000000'],
    ['receipts', [['R1', '1-4-2026', 'IMP-C', 'Customer', 'Receipt', '125', 'نقدي']], Receipt::class, '125.000000'],
    ['payments', [['V1', '1-4-2026', 'IMP-S', 'Supplier', 'Payment', '125', 'تحويل']], SupplierPayment::class, '125.000000'],
    ['expenses', [['E1', '1-4-2026', 'Rent', 'إيجار', '100', 'نقدي']], ExpenseClaim::class, '100.000000'],
]);

test('invalid rows block the whole import and preserve the preview', function (array $row) {
    importParty();
    Livewire::test(ImportWorkspace::class, ['kind' => 'receipts'])->set('options.cash_account', '110101')
        ->set('file', importFile('receipts', [$row]))->call('preview')->assertHasErrors('import')->assertSet('valid', false);
    expect(Receipt::count())->toBe(0)->and(SpreadsheetImport::count())->toBe(1);
})->with([
    [['R1', '', 'IMP-C', 'Customer', 'Missing date', '100', 'نقدي']],
    [['R1', '31-2-2026', 'IMP-C', 'Customer', 'Invalid date', '100', 'نقدي']],
    [['R1', '1-4-2026', 'IMP-C', 'Customer', 'Negative amount', '-1', 'نقدي']],
    [['R1', '1-4-2026', 'UNKNOWN', 'Customer', 'Unknown customer', '100', 'نقدي']],
    [['R1', '1-4-2026', 'IMP-C', 'Customer', 'Unknown method', '100', 'crypto']],
]);

test('permission revocation and context changes are enforced again at confirmation', function () {
    $component = Livewire::test(ImportWorkspace::class, ['kind' => 'customers'])->set('file', importFile('customers', [['CTX', 'Context', '', '0']]))->call('preview')->assertHasNoErrors();
    $component->set('acknowledged', true);
    AccessGrant::where('permission', 'ar.write')->delete();
    $component->call('confirm')->assertForbidden();
    expect(Customer::where('code', 'CTX')->exists())->toBeFalse();
});

test('report imports remain references and item definitions start at zero', function () {
    $journals = DB::table('journals')->count();
    foreach (['customer-report', 'supplier-report', 'inventory-report'] as $kind) {
        $row = $kind === 'inventory-report' ? ['IT1', 'Item', '0', '10', '2', '8', '800', '2', 'جيد'] : ['PARTY1', 'External party', '100', '300', '150', '250'];
        Livewire::test(ImportWorkspace::class, ['kind' => $kind])->set('file', importFile($kind, [$row]))->call('preview')->assertHasNoErrors()
            ->set('acknowledged', true)->call('confirm')->assertHasNoErrors();
    }
    Livewire::test(ImportWorkspace::class, ['kind' => 'items'])->set('options.inventory_account', '110301')->set('options.cogs_account', '510101')
        ->set('file', importFile('inventory-report', [['IT1', 'Item', '0', '10', '2', '8', '800', '2', 'جيد']]))->call('preview')->assertHasNoErrors()
        ->set('acknowledged', true)->call('confirm')->assertHasNoErrors();
    expect(InventoryItem::where('code', 'IT1')->firstOrFail()->quantity)->toBe('0.000000')
        ->and(DB::table('journals')->count())->toBe($journals)->and(Customer::count())->toBe(0);
});

test('item workbook stores catalog columns without journals or stock movement', function () {
    $journals = DB::table('journals')->count();
    $headers = ['كود الصنف', 'اسم الصنف', 'التصنيف', 'الوحدة', 'تكلفة الوحدة', 'سعر البيع', 'الحد الأدنى'];
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet('Sheet1', $headers, [
        ['1001', 'مالية نص', 'تيشرت', 'قطعة', '45', '75', '60'],
        ['1003', 'توتة كاملة', 'توتة', 'قطعة', '100', '150', '100'],
    ])]);
    Livewire::test(ImportWorkspace::class, ['kind' => 'items'])
        ->set('options.inventory_account', '110301')->set('options.cogs_account', '510101')
        ->set('file', UploadedFile::fake()->createWithContent('uat-items.xlsx', $bytes))
        ->call('preview')->assertHasNoErrors()->set('acknowledged', true)->call('confirm')->assertHasNoErrors();
    $item = InventoryItem::where('code', '1001')->firstOrFail();
    expect($item->name)->toBe('مالية نص')->and($item->category)->toBe('تيشرت')->and($item->unit)->toBe('قطعة')
        ->and($item->standard_cost)->toBe('45.000000')->and($item->sale_price)->toBe('75.000000')->and($item->reorder_level)->toBe('60.000000')
        ->and($item->quantity)->toBe('0.000000')->and($item->value)->toBe('0.000000')
        ->and(InventoryItem::where('code', '1003')->firstOrFail()->category)->toBe('توتة')
        ->and(DB::table('journals')->count())->toBe($journals)->and(DB::table('stock_moves')->count())->toBe(0);
});

test('item workbook rejects a negative catalog amount', function () {
    $headers = ['كود الصنف', 'اسم الصنف', 'التصنيف', 'الوحدة', 'تكلفة الوحدة', 'سعر البيع', 'الحد الأدنى'];
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet('Sheet1', $headers, [['1001', 'مالية نص', 'تيشرت', 'قطعة', '-45', '75', '60']])]);
    Livewire::test(ImportWorkspace::class, ['kind' => 'items'])
        ->set('options.inventory_account', '110301')->set('options.cogs_account', '510101')
        ->set('file', UploadedFile::fake()->createWithContent('uat-items.xlsx', $bytes))
        ->call('preview')->assertHasErrors('import');
    expect(InventoryItem::where('code', '1001')->exists())->toBeFalse();
});

test('missing import accounts fail once, not once per row', function () {
    $headers = ['كود الصنف', 'اسم الصنف', 'التصنيف', 'الوحدة', 'تكلفة الوحدة', 'سعر البيع', 'الحد الأدنى'];
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet('Sheet1', $headers, [
        ['1001', 'مالية نص', 'تيشرت', 'قطعة', '45', '75', '60'],
        ['1002', 'سروال', 'سروال', 'قطعة', '40', '75', '60'],
        ['1003', 'توتة كاملة', 'توتة', 'قطعة', '100', '150', '100'],
    ])]);
    $component = Livewire::test(ImportWorkspace::class, ['kind' => 'items'])
        ->set('file', UploadedFile::fake()->createWithContent('uat-items.xlsx', $bytes))
        ->call('preview')->assertHasErrors('import');
    expect($component->errors()->get('import'))->toBe([__('imports.account_required')]);
});

test('account pickers offer only what each field accepts and errors name the alternatives', function () {
    $component = Livewire::test(ImportWorkspace::class, ['kind' => 'items'])->assertOk();
    $offered = $component->viewData('accounts');
    expect($offered['inventory_account']->pluck('code')->all())->toContain('110301')->not->toContain('110202')
        ->and($offered['cogs_account']->pluck('code')->all())->toContain('510101');

    $headers = ['كود الصنف', 'اسم الصنف', 'التصنيف', 'الوحدة', 'تكلفة الوحدة', 'سعر البيع', 'الحد الأدنى'];
    $bytes = app(XlsxWriter::class)->render([new ExcelSheet('Sheet1', $headers, [['1001', 'مالية نص', 'تيشرت', 'قطعة', '45', '75', '60']])]);
    $failed = Livewire::test(ImportWorkspace::class, ['kind' => 'items'])
        ->set('options.inventory_account', '110202')->set('options.cogs_account', '510101')
        ->set('file', UploadedFile::fake()->createWithContent('uat-items.xlsx', $bytes))
        ->call('preview')->assertHasErrors('import');
    $message = $failed->errors()->get('import')[0];
    expect($message)->toContain('110202')->toContain('110301');
});

test('confirmation revalidates source codes and rolls back every record', function () {
    $component = Livewire::test(ImportWorkspace::class, ['kind' => 'customers'])
        ->set('file', importFile('customers', [['IMP-A', 'A', '', '0'], ['IMP-B', 'B', '', '0']]))->call('preview')->assertHasNoErrors();
    app(CustomerService::class)->create(Company::firstOrFail(), ['code' => 'IMP-B', 'name_ar' => 'Concurrent customer']);
    $component->set('acknowledged', true)->call('confirm')->assertHasErrors('import');
    expect(Customer::where('code', 'IMP-A')->exists())->toBeFalse()->and(DB::table('spreadsheet_import_keys')->count())->toBe(0);
});

test('reader rejects non xlsx and dates do not roll forward', function () {
    $file = UploadedFile::fake()->createWithContent('bad.xlsx', 'not a workbook');
    expect(fn () => app(XlsxReader::class)->read($file->getRealPath()))->toThrow(ValidationException::class);
    expect(fn () => app(SpreadsheetImporter::class)->date('12-7-2026.'))->toThrow(ValidationException::class);
    expect(app(SpreadsheetImporter::class)->date('28-6-2026'))->toBe('2026-06-28');
});
