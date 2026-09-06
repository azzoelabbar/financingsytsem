<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phases F–S support tables. Journals still post only through AccountingEngine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('kind'); // output_vat|input_vat|wht|cit|deferred
            $table->string('gl_account_code');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_code_id')->constrained('tax_codes')->cascadeOnDelete();
            $table->decimal('rate', 12, 6);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('legal_reference');
            $table->string('authority')->nullable();
            $table->string('status')->default('approved');
            $table->timestamps();
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('cost_account_code');
            $table->string('accum_account_code');
            $table->string('expense_account_code');
            $table->decimal('cost', 28, 6);
            $table->decimal('accum_depreciation', 28, 6)->default(0);
            $table->decimal('accum_impairment', 28, 6)->default(0);
            $table->unsignedSmallInteger('useful_life_months');
            $table->date('in_service_date');
            $table->string('status')->default('active'); // active|disposed
            $table->string('location')->nullable();
            $table->foreignId('acquisition_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('warehouse_code')->default('MAIN');
            $table->string('gl_account_code');
            $table->string('cogs_account_code');
            $table->decimal('quantity', 28, 6)->default(0);
            $table->decimal('value', 28, 6)->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'code', 'warehouse_code']);
        });

        Schema::create('stock_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('type'); // receipt|issue|transfer_out|transfer_in|adjust|write_down
            $table->date('move_date');
            $table->decimal('quantity', 28, 6);
            $table->decimal('unit_cost', 28, 6);
            $table->decimal('value', 28, 6);
            $table->string('warehouse_code');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('deferred_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('number');
            $table->date('start_date');
            $table->decimal('amount', 28, 6);
            $table->decimal('recognized', 28, 6)->default(0);
            $table->unsignedSmallInteger('periods');
            $table->unsignedSmallInteger('periods_recognized')->default(0);
            $table->string('unearned_account_code');
            $table->string('revenue_account_code');
            $table->string('funding_account_code');
            $table->string('status')->default('active');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('code');
            $table->date('commencement_date');
            $table->decimal('present_value', 28, 6);
            $table->decimal('liability', 28, 6);
            $table->decimal('rou_cost', 28, 6);
            $table->decimal('accum_depreciation', 28, 6)->default(0);
            $table->decimal('interest_rate', 12, 6);
            $table->unsignedSmallInteger('term_months');
            $table->string('status')->default('active');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('code');
            $table->decimal('principal', 28, 6);
            $table->decimal('outstanding', 28, 6);
            $table->string('liability_account_code');
            $table->string('status')->default('active');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->date('run_date');
            $table->decimal('gross', 28, 6);
            $table->decimal('employer_ss', 28, 6);
            $table->decimal('paye', 28, 6);
            $table->decimal('employee_ss', 28, 6);
            $table->decimal('net', 28, 6);
            $table->string('status')->default('posted');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('period_key'); // 2026-03
            $table->string('account_code');
            $table->decimal('amount', 28, 6);
            $table->timestamps();
            $table->unique(['company_id', 'book_id', 'period_key', 'account_code']);
        });

        Schema::create('access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('permission');
            $table->timestamps();
            $table->unique(['user_id', 'company_id', 'permission']);
        });

        Schema::create('risk_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('severity');
            $table->unsignedTinyInteger('score');
            $table->json('details')->nullable();
            $table->timestamps();
        });

        Schema::create('assistant_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('prompt');
            $table->string('status')->default('draft'); // draft|reviewed|approved|posted|rejected
            $table->json('proposed_lines');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_drafts');
        Schema::dropIfExists('risk_flags');
        Schema::dropIfExists('access_grants');
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('deferred_revenues');
        Schema::dropIfExists('stock_moves');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_codes');
    }
};
