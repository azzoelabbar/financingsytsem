<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bank statements, reconciliations, matches, and cash counts (Phase C).
 * INV-6: adjusted statement balance = book (GL / cash-transaction) balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treasury_account_id')->constrained('treasury_accounts')->cascadeOnDelete();
            $table->string('statement_number')->nullable();
            $table->date('statement_date');
            $table->date('period_start')->nullable();
            $table->date('period_end');
            $table->decimal('opening_balance', 28, 6)->default(0);
            $table->decimal('closing_balance', 28, 6)->default(0);
            $table->string('currency', 3)->default('LYD');
            $table->string('status')->default('imported'); // imported | reconciled
            $table->string('source')->default('manual'); // manual | import
            $table->timestamps();

            $table->index(['company_id', 'treasury_account_id', 'period_end']);
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->date('line_date');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 28, 6); // signed: + deposit, - withdrawal
            $table->string('direction'); // in | out
            $table->boolean('is_matched')->default(false);
            $table->timestamps();

            $table->index(['bank_statement_id', 'line_no']);
            $table->index(['bank_statement_id', 'is_matched']);
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('treasury_account_id')->constrained('treasury_accounts')->cascadeOnDelete();
            $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $table->date('as_of_date');
            $table->decimal('statement_balance', 28, 6)->default(0);
            $table->decimal('book_balance', 28, 6)->default(0);
            $table->decimal('outstanding_deposits', 28, 6)->default(0);
            $table->decimal('outstanding_cheques', 28, 6)->default(0);
            $table->decimal('adjusted_statement_balance', 28, 6)->default(0);
            $table->decimal('difference', 28, 6)->default(0);
            $table->string('status')->default('draft'); // draft | in_progress | completed
            $table->unsignedInteger('date_tolerance_days')->default(3);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'treasury_account_id', 'as_of_date']);
        });

        Schema::create('bank_reconciliation_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->foreignId('bank_statement_line_id')->constrained('bank_statement_lines')->cascadeOnDelete();
            $table->foreignId('cash_transaction_id')->nullable()->constrained('cash_transactions')->nullOnDelete();
            $table->string('match_type'); // exact | amount | reference | manual
            $table->decimal('amount', 28, 6);
            $table->timestamps();

            $table->unique(['bank_statement_line_id']);
            $table->index('cash_transaction_id');
        });

        Schema::create('cash_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('treasury_account_id')->constrained('treasury_accounts')->cascadeOnDelete();
            $table->date('count_date');
            $table->decimal('system_balance', 28, 6)->default(0);
            $table->decimal('counted_balance', 28, 6)->default(0);
            $table->decimal('difference', 28, 6)->default(0);
            $table->string('status')->default('draft'); // draft | posted
            $table->string('adjustment_account_code')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'treasury_account_id', 'count_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_counts');
        Schema::dropIfExists('bank_reconciliation_matches');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
    }
};
