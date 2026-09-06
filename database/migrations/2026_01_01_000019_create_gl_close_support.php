<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase D — GL close support: recurring templates, accruals, prepayments,
 * and period-close checklist runs. Journals themselves already live in 000005.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_journal_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('frequency')->default('monthly'); // monthly|quarterly|yearly
            $table->unsignedTinyInteger('day_of_month')->default(1);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date')->nullable();
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('recurring_journal_template_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_journal_template_id')->constrained('recurring_journal_templates')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->string('account_code');
            $table->decimal('debit', 28, 6)->default(0);
            $table->decimal('credit', 28, 6)->default(0);
            $table->string('description')->nullable();
            $table->json('dimensions')->nullable();
            $table->timestamps();

            $table->index(['recurring_journal_template_id', 'line_no'], 'rjt_lines_idx');
        });

        Schema::create('accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('number')->nullable();
            $table->date('accrual_date');
            $table->date('reversal_date')->nullable();
            $table->string('expense_account_code');
            $table->string('accrual_account_code');
            $table->decimal('amount', 28, 6);
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->string('description')->nullable();
            $table->string('status')->default('draft'); // draft|posted|reversed
            $table->boolean('auto_reverse')->default(true);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('reversal_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('prepayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('number')->nullable();
            $table->date('prepayment_date');
            $table->string('prepaid_account_code');
            $table->string('expense_account_code');
            $table->string('funding_account_code'); // bank/cash credited on recognition
            $table->decimal('amount', 28, 6);
            $table->unsignedSmallInteger('periods')->default(1);
            $table->unsignedSmallInteger('periods_recognized')->default(0);
            $table->decimal('amount_recognized', 28, 6)->default(0);
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->string('description')->nullable();
            $table->string('status')->default('draft'); // draft|active|completed
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('prepayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prepayment_id')->constrained('prepayments')->cascadeOnDelete();
            $table->unsignedSmallInteger('period_no');
            $table->date('recognize_date');
            $table->decimal('amount', 28, 6);
            $table->string('status')->default('pending'); // pending|posted
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['prepayment_id', 'period_no']);
        });

        Schema::create('period_close_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('fiscal_period_id')->constrained('fiscal_periods')->cascadeOnDelete();
            $table->string('status')->default('in_progress'); // in_progress|passed|failed|closed
            $table->string('target_status')->default('soft_closed'); // soft_closed|hard_closed|locked
            $table->boolean('all_passed')->default(false);
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'fiscal_period_id']);
        });

        Schema::create('period_close_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_close_run_id')->constrained('period_close_runs')->cascadeOnDelete();
            $table->string('code'); // AR_RECON|AP_RECON|BANK_RECON|INTEGRITY|TB|FS|SUSPENSE|ACCRUALS|PREPAYMENTS
            $table->string('name');
            $table->string('status')->default('pending'); // pending|pass|fail|skipped
            $table->string('message')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->unique(['period_close_run_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_close_checklist_items');
        Schema::dropIfExists('period_close_runs');
        Schema::dropIfExists('prepayment_schedules');
        Schema::dropIfExists('prepayments');
        Schema::dropIfExists('accruals');
        Schema::dropIfExists('recurring_journal_template_lines');
        Schema::dropIfExists('recurring_journal_templates');
    }
};
