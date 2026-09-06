<?php

namespace App\Providers;

use App\Enums\Accounting\BookBasis;
use App\Services\Accounting\Engine\RuleRegistry;
use App\Services\Accounting\Rules\AccrualRule;
use App\Services\Accounting\Rules\BadDebtWriteOffRule;
use App\Services\Accounting\Rules\CustomerReceiptRule;
use App\Services\Accounting\Rules\EclRule;
use App\Services\Accounting\Rules\FxRealizedRule;
use App\Services\Accounting\Rules\FxRevaluationRule;
use App\Services\Accounting\Rules\FxTranslationRule;
use App\Services\Accounting\Rules\PayloadMovementsRule;
use App\Services\Accounting\Rules\PrepaymentAmortizationRule;
use App\Services\Accounting\Rules\PrepaymentRule;
use App\Services\Accounting\Rules\PurchaseCreditNoteRule;
use App\Services\Accounting\Rules\PurchaseDebitNoteRule;
use App\Services\Accounting\Rules\PurchaseInvoiceRule;
use App\Services\Accounting\Rules\SalesCreditNoteRule;
use App\Services\Accounting\Rules\SalesDebitNoteRule;
use App\Services\Accounting\Rules\SalesInvoiceRule;
use App\Services\Accounting\Rules\SupplierPaymentRule;
use App\Services\Accounting\Rules\TreasuryPaymentRule;
use App\Services\Accounting\Rules\TreasuryReceiptRule;
use App\Services\Accounting\Rules\TreasuryTransferRule;
use App\Support\Registration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The Accounting Rules registry: the single place source-transaction
        // types are mapped to balanced-journal rules (spec §48). Modules post
        // through the AccountingEngine, never by building journals themselves.
        $this->app->singleton(RuleRegistry::class, function (): RuleRegistry {
            $registry = new RuleRegistry;
            $registry->register(new SalesInvoiceRule);
            $registry->register(new SalesCreditNoteRule);
            $registry->register(new SalesDebitNoteRule);
            $registry->register(new CustomerReceiptRule);
            $registry->register(new BadDebtWriteOffRule);
            $registry->register(new EclRule);
            $registry->register(new PurchaseInvoiceRule);
            $registry->register(new PurchaseCreditNoteRule);
            $registry->register(new PurchaseDebitNoteRule);
            $registry->register(new SupplierPaymentRule);
            $registry->register(new TreasuryReceiptRule);
            $registry->register(new TreasuryPaymentRule);
            $registry->register(new TreasuryTransferRule);
            $registry->register(new AccrualRule);
            $registry->register(new PrepaymentRule);
            $registry->register(new PrepaymentAmortizationRule);
            $registry->register(new FxRevaluationRule);
            $registry->register(new FxRealizedRule);
            $registry->register(new FxTranslationRule);
            foreach ([
                'tax.settlement' => 'tax',
                'tax.withholding' => 'tax',
                'tax.adjustment' => 'tax',
                'tax.deferred' => 'tax',
                'fa.acquire' => 'fixed_asset',
                'fa.depreciate' => 'fixed_asset',
                'fa.impair' => 'fixed_asset',
                'fa.dispose' => 'fixed_asset',
                'inventory.receipt' => 'inventory',
                'inventory.issue' => 'inventory',
                'inventory.adjust' => 'inventory',
                'revenue.defer' => 'revenue',
                'revenue.recognize' => 'revenue',
                'lease.commence' => 'lease',
                'lease.interest' => 'lease',
                'lease.payment' => 'lease',
                'lease.depreciate' => 'lease',
                'loan.drawdown' => 'loan',
                'loan.repay' => 'loan',
                'payroll.accrue' => 'payroll',
                'payroll.pay' => 'payroll',
                'cost.allocate' => 'cost',
                'consolidation.eliminate' => 'consolidation',
                'investment.acquire' => 'investment',
                'investment.revalue' => 'investment',
                'investment.dividend' => 'investment',
                'investment.interest' => 'investment',
                'investment.dispose' => 'investment',
                'expense.claim' => 'expense',
                'expense.reimburse' => 'expense',
                'project.cost' => 'project',
                'project.capitalize' => 'project',
                'opening.balance' => 'opening',
            ] as $type => $source) {
                foreach (BookBasis::cases() as $basis) {
                    $registry->register(new PayloadMovementsRule($type, $source, $basis));
                }
            }

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Registration happens once on this install: only offer the sign-up
        // affordances while no account exists yet.
        Blade::if('registrationOpen', fn (): bool => Registration::isOpen());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
