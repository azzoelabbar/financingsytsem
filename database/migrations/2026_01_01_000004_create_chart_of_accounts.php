<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chart of Accounts (spec §3-4). Hierarchical, configurable, multi-level and
 * metadata-rich. Structure preserved from the supplied Libyan chart:
 *   1 digit = class, 2 = group, 4 = account, 6 = detailed/posting account.
 *
 * Posting is only permitted on accounts flagged is_posting = true; a parent
 * (summary) account can never be posted to directly (spec §3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // --- Identity & hierarchy ---
            $table->string('code');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1); // 1..n by code length

            // --- Classification (nature is canonical, not the name — spec §66) ---
            $table->string('account_type');    // asset|liability|equity|revenue|cost_of_sales|expense|closing
            $table->string('normal_balance');  // debit|credit|none
            $table->string('statement');       // balance_sheet|income_statement|none
            $table->string('closing_behavior')->default('permanent');

            // --- Posting control ---
            $table->boolean('is_posting')->default(false);         // detail/ledger account
            $table->boolean('is_control')->default(false);         // control account backed by a subledger
            $table->boolean('is_contra')->default(false);          // contra account (deducted, not added)
            $table->foreignId('contra_of_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_statistical')->default(false);     // memo/off-balance-sheet, non-financial

            // --- Reporting classification ---
            $table->string('financial_statement_line')->nullable(); // FS line mapping
            $table->string('cash_flow_classification')->nullable(); // operating|investing|financing
            $table->string('ifrs_reference')->nullable();           // IFRS/IAS mapping (spec §5)
            $table->string('tax_classification')->nullable();
            $table->string('cost_classification')->nullable();

            // --- Dimension requirements (spec §38) ---
            $table->boolean('requires_cost_center')->default(false);
            $table->boolean('requires_project')->default(false);
            $table->boolean('requires_branch')->default(false);
            $table->boolean('requires_department')->default(false);

            // --- Subledger / behaviour flags (spec §4) ---
            $table->boolean('is_bank_account')->default(false);
            $table->boolean('is_customer_subledger')->default(false);
            $table->boolean('is_supplier_subledger')->default(false);
            $table->boolean('is_asset_subledger')->default(false);
            $table->boolean('is_tax_account')->default(false);
            $table->boolean('reconciliation_required')->default(false);
            $table->string('currency_mode')->default('any'); // any|single|functional_only
            $table->string('single_currency', 3)->nullable();

            // --- Journal permissions ---
            $table->boolean('opening_balance_allowed')->default(true);
            $table->boolean('manual_journal_allowed')->default(true);
            $table->boolean('system_generated_only')->default(false);

            // --- Lifecycle ---
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('regulatory_mapping')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'account_type']);
            $table->index(['company_id', 'is_posting']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
