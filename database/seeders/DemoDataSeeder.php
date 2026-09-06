<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Accounting\RateType;
use App\Enums\Treasury\CashTransactionType;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\SalesInvoice;
use App\Services\Ap\PurchaseInvoiceService;
use App\Services\Ap\SupplierPaymentService;
use App\Services\Ap\SupplierService;
use App\Services\Ar\CreditNoteService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\DebitNoteService;
use App\Services\Ar\ReceiptService;
use App\Services\Ar\SalesInvoiceService;
use App\Services\Assets\AssetService;
use App\Services\Budget\BudgetService;
use App\Services\Expense\ExpenseClaimService;
use App\Services\Expense\ExpenseReimbursementService;
use App\Services\Fx\ExchangeRateService;
use App\Services\Gl\OpeningBalanceService;
use App\Services\Investment\InvestmentService;
use App\Services\Project\ProjectService;
use App\Services\Tax\TaxEngine;
use App\Services\Treasury\BankAccountService;
use App\Services\Treasury\CashTransactionService;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Coherent, accounting-consistent demo dataset built entirely through the real
 * application/domain services (every posted document creates a real journal, so
 * invariants — Debit=Credit, AR/AP subledger=control, BS balances — hold).
 *
 * Reproducible: run after AccountingReferenceSeeder + DemoCompanySeeder, e.g.
 *   php artisan migrate:fresh --seed
 * (DatabaseSeeder chains them) or `php artisan db:seed --class=DemoDataSeeder`.
 * Idempotent guard: skips if demo customers already exist.
 */
class DemoDataSeeder extends Seeder
{
    private Company $company;

    private AccountingBook $local;

    public function run(): void
    {
        $company = Company::query()->first();
        if ($company === null) {
            return;
        }
        $this->company = $company;
        $this->local = AccountingBook::query()->where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();

        if (Customer::query()->where('company_id', $company->id)->where('code', 'DEMO-C1')->exists()) {
            return; // already seeded
        }

        $this->fxRates();
        $this->openingBalances();
        $this->accountsReceivable();
        $this->accountsPayable();
        $this->banking();
        $this->expenses();
        $this->projects();
        $this->investments();
        $this->fixedAssets();
        $this->tax();
        $this->budgets();
    }

    private function fxRates(): void
    {
        $fx = app(ExchangeRateService::class);
        foreach ([['USD', '4.85'], ['EUR', '5.25']] as [$ccy, $rate]) {
            try {
                $fx->setRate($this->company, $ccy, 'LYD', $rate, '2026-01-01', RateType::SPOT, 'CBL');
            } catch (Throwable) {
            }
        }
    }

    private function openingBalances(): void
    {
        try {
            app(OpeningBalanceService::class)->post($this->company, $this->local, '2026-01-01', [
                ['account' => '110102', 'debit' => 250000],
                ['account' => '310101', 'credit' => 250000],
            ]);
        } catch (Throwable) {
        }

        // A second, still-draft batch to demonstrate the validate → post → lock workflow.
        try {
            $svc = app(OpeningBalanceService::class);
            $batch = $svc->createDraft($this->company, $this->local, '2026-01-01', 'LYD');
            $svc->addLine($batch, ['account' => '110101', 'debit' => 15000]);
            $svc->addLine($batch, ['account' => '310101', 'credit' => 15000]);
            $svc->validate($batch->fresh());
        } catch (Throwable) {
        }
    }

    private function accountsReceivable(): void
    {
        $customers = app(CustomerService::class);
        $invoices = app(SalesInvoiceService::class);
        $receipts = app(ReceiptService::class);

        $c1 = $customers->create($this->company, ['code' => 'DEMO-C1', 'name_ar' => 'شركة النور للتجارة', 'name_en' => 'Al-Noor Trading', 'currency' => 'LYD']);
        $c2 = $customers->create($this->company, ['code' => 'DEMO-C2', 'name_ar' => 'مجموعة الأطلس', 'name_en' => 'Atlas Group', 'currency' => 'USD']);
        $c3 = $customers->create($this->company, ['code' => 'DEMO-C3', 'name_ar' => 'دار البيان', 'name_en' => 'Dar Al-Bayan', 'currency' => 'EUR']);
        $c4 = $customers->create($this->company, ['code' => 'DEMO-C4', 'name_ar' => 'شركة الواحة', 'name_en' => 'Oasis Co.', 'currency' => 'LYD']);

        // Open (current)
        $this->postInvoice($invoices, $c1, [['revenue_account' => '410101', 'net' => 8200, 'tax_account' => '210404', 'tax' => 410]], ['invoice_date' => '2026-08-20', 'due_date' => '2026-09-19']);

        // Overdue + partially paid
        $inv2 = $this->postInvoice($invoices, $c1, [['revenue_account' => '410101', 'net' => 12000, 'tax_account' => '210404', 'tax' => 600]], ['invoice_date' => '2026-06-10', 'due_date' => '2026-07-10']);
        $r2 = $receipts->post($receipts->createDraft($this->company, $this->local, $c1, 5000, ['receipt_date' => '2026-07-15']));
        $receipts->allocate($r2, $inv2, 5000);

        // Overdue + fully paid
        $inv3 = $this->postInvoice($invoices, $c4, [['revenue_account' => '410101', 'net' => 6000, 'tax_account' => '210404', 'tax' => 300]], ['invoice_date' => '2026-07-05', 'due_date' => '2026-08-04']);
        $r3 = $receipts->post($receipts->createDraft($this->company, $this->local, $c4, 6300, ['receipt_date' => '2026-08-01']));
        $receipts->allocate($r3, $inv3, 6300);

        // USD open invoice
        $this->postInvoice($invoices, $c2, [['revenue_account' => '410101', 'net' => 4000, 'tax' => 0]], ['invoice_date' => '2026-08-25', 'due_date' => '2026-09-24', 'currency' => 'USD', 'exchange_rate' => 4.85]);

        // EUR open invoice
        $this->postInvoice($invoices, $c3, [['revenue_account' => '410101', 'net' => 3000, 'tax' => 0]], ['invoice_date' => '2026-08-15', 'due_date' => '2026-09-14', 'currency' => 'EUR', 'exchange_rate' => 5.25]);

        // A draft (unposted) invoice
        try {
            $invoices->createDraft($this->company, $this->local, $c1, [['revenue_account' => '410101', 'net' => 2500, 'tax_account' => '210404', 'tax' => 125]], ['invoice_date' => '2026-09-01', 'due_date' => '2026-10-01']);
        } catch (Throwable) {
        }

        // Credit + debit note against the first customer
        try {
            $cn = app(CreditNoteService::class)->post(app(CreditNoteService::class)->createDraft($this->company, $this->local, $c1, [['revenue_account' => '410101', 'net' => 500, 'tax' => 0]], ['credit_note_date' => '2026-08-22', 'reason' => 'خصم كمية']));
            app(CreditNoteService::class)->allocate($cn, $inv2->fresh(), 500);
        } catch (Throwable) {
        }
        try {
            app(DebitNoteService::class)->post(app(DebitNoteService::class)->createDraft($this->company, $this->local, $c1, [['revenue_account' => '410101', 'net' => 300, 'tax' => 0]], ['debit_note_date' => '2026-08-23', 'reason' => 'رسوم شحن']));
        } catch (Throwable) {
        }
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    private function postInvoice(SalesInvoiceService $svc, Customer $customer, array $lines, array $header): SalesInvoice
    {
        return $svc->post($svc->createDraft($this->company, $this->local, $customer, $lines, $header));
    }

    private function accountsPayable(): void
    {
        $suppliers = app(SupplierService::class);
        $bills = app(PurchaseInvoiceService::class);
        $payments = app(SupplierPaymentService::class);

        $s1 = $suppliers->create($this->company, ['code' => 'DEMO-S1', 'legal_name' => 'مؤسسة الإمداد', 'trading_name' => 'Imdad Supplies', 'currency' => 'LYD']);
        $s2 = $suppliers->create($this->company, ['code' => 'DEMO-S2', 'legal_name' => 'شركة المعدات الدولية', 'trading_name' => 'Global Equipment', 'currency' => 'USD']);

        // Open bill
        $bills->post($bills->createDraft($this->company, $this->local, $s1, [['expense_account' => '620201', 'net' => 4500, 'tax' => 0]], ['invoice_date' => '2026-08-18', 'due_date' => '2026-09-17', 'supplier_invoice_number' => 'S1-1001']));

        // Overdue + partially paid
        $bill2 = $bills->post($bills->createDraft($this->company, $this->local, $s1, [['expense_account' => '620201', 'net' => 9000, 'tax' => 0]], ['invoice_date' => '2026-06-20', 'due_date' => '2026-07-20', 'supplier_invoice_number' => 'S1-1002']));
        $p2 = $payments->post($payments->createDraft($this->company, $this->local, $s1, 4000, ['payment_date' => '2026-07-25']));
        $payments->allocate($p2, $bill2, 4000);

        // Fully paid
        $bill3 = $bills->post($bills->createDraft($this->company, $this->local, $s2, [['expense_account' => '620201', 'net' => 3000, 'tax' => 0]], ['invoice_date' => '2026-07-10', 'due_date' => '2026-08-09', 'supplier_invoice_number' => 'S2-2001']));
        $p3 = $payments->post($payments->createDraft($this->company, $this->local, $s2, 3000, ['payment_date' => '2026-08-05']));
        $payments->allocate($p3, $bill3, 3000);
    }

    private function banking(): void
    {
        try {
            $svc = app(BankAccountService::class);
            $bank = $svc->createBank($this->company, ['code' => 'JBANK', 'name_ar' => 'مصرف الجمهورية', 'name_en' => 'Jumhouria Bank', 'country' => 'LY']);
            $bankAcc = $svc->createAccount($this->company, ['bank_id' => $bank->id, 'code' => 'BANK-LYD', 'name_ar' => 'الحساب الجاري', 'name_en' => 'Current Account', 'type' => 'bank', 'currency' => 'LYD', 'gl_account_code' => '110102', 'opening_balance' => 120000, 'opening_balance_date' => '2026-01-01']);
            $svc->createAccount($this->company, ['code' => 'CASH-MAIN', 'name_ar' => 'الصندوق الرئيسي', 'name_en' => 'Main Cash', 'type' => 'cash', 'currency' => 'LYD', 'gl_account_code' => '110101', 'opening_balance' => 8000, 'opening_balance_date' => '2026-01-01']);

            $tx = app(CashTransactionService::class);
            $t1 = $tx->createDraft($this->company, $this->local, $bankAcc, CashTransactionType::BANK_RECEIPT, 3500, ['transaction_date' => '2026-08-12', 'counter_account_code' => '410101', 'reference' => 'إيداع نقدي']);
            $tx->post($t1);
            $t2 = $tx->createDraft($this->company, $this->local, $bankAcc, CashTransactionType::BANK_PAYMENT, 1200, ['transaction_date' => '2026-08-19', 'counter_account_code' => '620201', 'reference' => 'مصروفات تشغيل']);
            $tx->post($t2);
            $tx->post($tx->createDraft($this->company, $this->local, $bankAcc, CashTransactionType::BANK_FEE, 45, ['transaction_date' => '2026-08-31', 'reference' => 'رسوم بنكية']));
        } catch (Throwable) {
        }
    }

    private function expenses(): void
    {
        $svc = app(ExpenseClaimService::class);
        $line = [['amount' => 320, 'expense_account' => '620201', 'description' => 'سفر وإقامة']];

        try {
            // draft
            $svc->createDraft($this->company, $this->local, ['number' => 'EXP-DEMO-1', 'employee_ref' => 'EMP-014', 'claim_date' => '2026-09-01'], $line);
            // submitted
            $c2 = $svc->createDraft($this->company, $this->local, ['number' => 'EXP-DEMO-2', 'employee_ref' => 'EMP-022', 'claim_date' => '2026-08-28'], $line);
            $svc->submit($c2);
            // approved
            $c3 = $svc->createDraft($this->company, $this->local, ['number' => 'EXP-DEMO-3', 'employee_ref' => 'EMP-031', 'claim_date' => '2026-08-20'], $line);
            $svc->approve($svc->submit($c3)->fresh());
            // rejected
            $c4 = $svc->createDraft($this->company, $this->local, ['number' => 'EXP-DEMO-4', 'employee_ref' => 'EMP-009', 'claim_date' => '2026-08-15'], $line);
            $svc->reject($svc->submit($c4)->fresh());
            // posted + reimbursed
            $c5 = $svc->createDraft($this->company, $this->local, ['number' => 'EXP-DEMO-5', 'employee_ref' => 'EMP-040', 'claim_date' => '2026-08-10'], $line);
            $svc->submit($c5);
            $svc->approve($c5->fresh());
            $posted = $svc->post($c5->fresh());
            app(ExpenseReimbursementService::class)->reimburse($posted->fresh(), '2026-08-18');
        } catch (Throwable) {
        }
    }

    private function projects(): void
    {
        try {
            $svc = app(ProjectService::class);
            $p1 = $svc->define($this->company, ['code' => 'PRJ-ERP', 'name' => 'تطبيق نظام ERP', 'budget' => 80000]);
            $svc->charge($p1, '2026-07-15', 22000);
            $svc->charge($p1->fresh(), '2026-08-10', 18000);
            $svc->capitalize($p1->fresh(), '2026-08-20', 30000);

            $p2 = $svc->define($this->company, ['code' => 'PRJ-WH', 'name' => 'توسعة المستودع', 'budget' => 45000]);
            $svc->charge($p2, '2026-08-05', 12500);
        } catch (Throwable) {
        }
    }

    private function investments(): void
    {
        try {
            $svc = app(InvestmentService::class);
            $i1 = $svc->acquire($this->company, $this->local, ['code' => 'INV-EQ1', 'name' => 'أسهم شركة مدرجة', 'classification' => 'FVTPL', 'cost' => 50000, 'date' => '2026-05-02']);
            $svc->revalue($i1, '2026-08-31', 54500);

            $i2 = $svc->acquire($this->company, $this->local, ['code' => 'INV-BOND', 'name' => 'سندات حكومية', 'classification' => 'FVOCI', 'cost' => 75000, 'date' => '2026-04-15']);
            $svc->revalue($i2, '2026-08-31', 73800);

            $svc->acquire($this->company, $this->local, ['code' => 'INV-TB', 'name' => 'أذون خزينة', 'classification' => 'AMORTIZED_COST', 'cost' => 40000, 'date' => '2026-06-01']);
        } catch (Throwable) {
        }
    }

    private function fixedAssets(): void
    {
        // Acquire against sundry creditors (210103), NOT the AP control (210101),
        // so the AP subledger = AP control invariant is preserved.
        $ap = ['ap_account_code' => '210103'];
        try {
            $svc = app(AssetService::class);
            $a1 = $svc->acquire($this->company, $this->local, ['code' => 'FA-VEH-01', 'name' => 'سيارة نقل', 'cost' => 60000, 'useful_life_months' => 60, 'in_service_date' => '2026-02-01', 'location' => 'طرابلس'] + $ap);
            $svc->depreciate($a1->fresh(), '2026-08-31');
            $a2 = $svc->acquire($this->company, $this->local, ['code' => 'FA-EQ-02', 'name' => 'معدات مكتبية', 'cost' => 24000, 'useful_life_months' => 48, 'in_service_date' => '2026-03-15', 'location' => 'المكتب الرئيسي'] + $ap);
            $svc->depreciate($a2->fresh(), '2026-08-31');
            $svc->acquire($this->company, $this->local, ['code' => 'FA-IT-03', 'name' => 'أجهزة حاسوب', 'cost' => 18000, 'useful_life_months' => 36, 'in_service_date' => '2026-06-01', 'location' => 'قسم التقنية'] + $ap);
        } catch (Throwable) {
        }
    }

    private function tax(): void
    {
        try {
            $tax = app(TaxEngine::class);
            $vat = $tax->defineCode($this->company, ['code' => 'VAT-OUT', 'name' => 'ضريبة القيمة المضافة - مخرجات', 'kind' => 'output_vat', 'gl_account_code' => '210404']);
            $tax->approveRate($vat, ['rate' => '0.05', 'effective_from' => '2026-01-01', 'legal_reference' => 'قانون الضريبة رقم 7', 'authority' => 'مصلحة الضرائب']);
            $wht = $tax->defineCode($this->company, ['code' => 'WHT-5', 'name' => 'ضريبة استقطاع', 'kind' => 'wht', 'gl_account_code' => '210405']);
            $tax->approveRate($wht, ['rate' => '0.05', 'effective_from' => '2026-01-01', 'legal_reference' => 'لائحة الاستقطاع']);
        } catch (Throwable) {
        }
    }

    private function budgets(): void
    {
        try {
            $svc = app(BudgetService::class);
            foreach ([['620201', 60000], ['410101', 200000]] as [$acct, $amt]) {
                $svc->set($this->company, $this->local, '2026', $acct, $amt);
            }
        } catch (Throwable) {
        }
    }
}
