<?php

declare(strict_types=1);

use App\Livewire\Imports\InventoryIndex;
use App\Livewire\Imports\ItemForm;
use App\Models\Accounting\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([AccountingReferenceSeeder::class, DemoCompanySeeder::class, DemoUserSeeder::class]);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());
});

test('an item is created from the form with zero balance and no journal', function () {
    $journals = DB::table('journals')->count();
    Livewire::test(ItemForm::class)
        ->set('code', '1001')->set('name', 'مالية نص')->set('category', 'تيشرت')->set('unit', 'قطعة')
        ->set('gl_account_code', '110301')->set('cogs_account_code', '510101')
        ->set('standard_cost', '45')->set('sale_price', '75')->set('reorder_level', '60')
        ->call('save')->assertHasNoErrors()->assertRedirect(route('imports.inventory'));

    $item = InventoryItem::where('code', '1001')->firstOrFail();
    expect($item->name)->toBe('مالية نص')->and($item->category)->toBe('تيشرت')->and($item->unit)->toBe('قطعة')
        ->and($item->standard_cost)->toBe('45.000000')->and($item->sale_price)->toBe('75.000000')->and($item->reorder_level)->toBe('60.000000')
        ->and($item->quantity)->toBe('0.000000')->and($item->value)->toBe('0.000000')
        ->and(DB::table('journals')->count())->toBe($journals);
});

test('the form loads an item and saves the edit without touching quantity or value', function () {
    $item = app(InventoryService::class)->defineItem(Company::firstOrFail(), ['code' => '1001', 'name' => 'مالية نص', 'category' => 'تيشرت', 'sale_price' => '75']);
    $component = Livewire::test(ItemForm::class, ['item' => $item])
        ->assertSet('code', '1001')->assertSet('category', 'تيشرت')->assertSet('sale_price', '75');
    $component->set('name', 'مالية نص معدّلة')->set('sale_price', '90')->set('category', '')
        ->call('save')->assertHasNoErrors();

    $item->refresh();
    expect($item->name)->toBe('مالية نص معدّلة')->and($item->sale_price)->toBe('90.000000')->and($item->category)->toBeNull()
        ->and($item->quantity)->toBe('0.000000')->and($item->value)->toBe('0.000000');
});

test('duplicate codes and unusable accounts are refused with the alternatives named', function () {
    app(InventoryService::class)->defineItem(Company::firstOrFail(), ['code' => '1001', 'name' => 'مالية نص']);
    Livewire::test(ItemForm::class)->set('code', '1001')->set('name', 'آخر')
        ->set('gl_account_code', '110301')->set('cogs_account_code', '510101')
        ->call('save')->assertHasErrors('code');

    $failed = Livewire::test(ItemForm::class)->set('code', '2001')->set('name', 'صنف')
        ->set('gl_account_code', '110202')->set('cogs_account_code', '510101')
        ->call('save')->assertHasErrors('gl_account_code');
    expect($failed->errors()->first('gl_account_code'))->toContain('110202')->toContain('110301');
    expect(InventoryItem::where('code', '2001')->exists())->toBeFalse();
});

test('a code backed by stock movements cannot be renamed', function () {
    $item = app(InventoryService::class)->defineItem(Company::firstOrFail(), ['code' => '1001', 'name' => 'مالية نص']);
    app(InventoryService::class)->receive($item, '2026-01-15', '10', '45');

    Livewire::test(ItemForm::class, ['item' => $item->fresh()])
        ->assertSet('codeLocked', true)
        ->set('code', 'CHANGED')->set('name', 'مالية نص')
        ->call('save')->assertHasNoErrors();

    expect($item->fresh()->code)->toBe('1001');
});

test('the items list offers create and edit to a writer', function () {
    app(InventoryService::class)->defineItem(Company::firstOrFail(), ['code' => '1001', 'name' => 'مالية نص']);
    Livewire::test(InventoryIndex::class)->assertOk()
        ->assertSee(__('imports.item_create'))
        ->assertSee(route('imports.items.edit', InventoryItem::where('code', '1001')->firstOrFail()), escape: false);
});

test('the create and edit routes resolve', function () {
    $item = app(InventoryService::class)->defineItem(Company::firstOrFail(), ['code' => '1001', 'name' => 'مالية نص']);
    $this->get(route('imports.items.create'))->assertOk()->assertSee(__('imports.item_create'));
    $this->get(route('imports.items.edit', $item))->assertOk()->assertSee('1001');
});
