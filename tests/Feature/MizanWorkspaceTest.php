<?php

declare(strict_types=1);

use App\Livewire\Ap\PurchaseInvoiceIndex;
use App\Livewire\Ap\SupplierIndex;
use App\Livewire\Ar\CustomerIndex;
use App\Livewire\Ar\SalesInvoiceIndex;
use App\Models\User;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('Mizan workspace and navigation render in both interface directions', function (string $locale, string $direction) {
    $this->seed([AccountingReferenceSeeder::class, DemoCompanySeeder::class, DemoUserSeeder::class]);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());

    $this->withSession(['locale' => $locale])->get(route('ar.receipts.create'))
        ->assertOk()
        ->assertSee('dir="'.$direction.'"', false)
        ->assertSee('x-data="mizanShell"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('id="command-results"', false)
        ->assertSee('data-command-source', false)
        ->assertSee('class="erp-sidebar-link erp-sidebar-link-active', false);
})->with([['ar', 'rtl'], ['en', 'ltr']]);

test('financial values retain a negative sign and semantic marker on dark surfaces', function () {
    $html = Blade::render('<x-ui.money amount="-1234.5" currency="LYD" />');

    expect($html)->toContain('−1,234.50', 'LYD', 'data-negative="true"', 'whitespace-nowrap', 'text-[var(--danger)]');
});

test('a filtered empty list explains that there are no matches', function (string $component) {
    $this->seed([AccountingReferenceSeeder::class, DemoCompanySeeder::class, DemoUserSeeder::class]);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());

    Livewire::test($component)
        ->set('search', 'NO-MIZAN-MATCH')
        ->assertSee(__('erp.filter.no_matches_title'))
        ->assertSee(__('erp.filter.clear'));
})->with([
    SalesInvoiceIndex::class,
    PurchaseInvoiceIndex::class,
    CustomerIndex::class,
    SupplierIndex::class,
]);

test('scrollable financial tables can have a specific accessible name', function () {
    $html = Blade::render('<x-ui.table aria-label="Invoice lines"><tbody><tr><td>100.00</td></tr></tbody></x-ui.table>');

    expect($html)->toContain('tabindex="0"', 'aria-label="Invoice lines"', 'overflow-x-auto')
        ->and(substr_count($html, 'aria-label='))->toBe(1);
});
