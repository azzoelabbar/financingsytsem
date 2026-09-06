<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Double-entry journals (spec §9). Every transaction produces a balanced entry:
 *   SUM(functional_debit) = SUM(functional_credit).
 * Posted journals are immutable — correction is by reversal/adjustment, never
 * DELETE (spec §31, §57).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('fiscal_period_id')->nullable()->constrained('fiscal_periods')->nullOnDelete();

            $table->string('number')->nullable(); // assigned on posting
            $table->date('journal_date');
            $table->date('posting_date')->nullable();
            $table->date('document_date')->nullable();

            $table->string('source')->default('manual'); // manual|sales|purchase|payment|payroll|asset|fx|closing...
            $table->string('reference')->nullable();
            $table->text('description')->nullable();

            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);

            $table->string('status')->default('draft'); // draft|pending|approved|posted|reversed|void
            $table->decimal('total_debit', 28, 6)->default(0);   // functional currency
            $table->decimal('total_credit', 28, 6)->default(0);  // functional currency

            // Reversal linkage (spec §31)
            $table->foreignId('reversal_of_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();

            // Governance
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();

            $table->boolean('is_system_generated')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'book_id', 'number']);
            $table->index(['company_id', 'book_id', 'status']);
            $table->index(['company_id', 'journal_date']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->unsignedInteger('line_no');
            $table->text('description')->nullable();

            // Transaction-currency amounts
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->decimal('debit', 28, 6)->default(0);
            $table->decimal('credit', 28, 6)->default(0);

            // Functional-currency amounts (base of the balancing rule)
            $table->decimal('functional_debit', 28, 6)->default(0);
            $table->decimal('functional_credit', 28, 6)->default(0);

            $table->timestamps();

            $table->index(['journal_id', 'line_no']);
            $table->index('account_id');
        });

        // Analytical dimensions per line (spec §38)
        Schema::create('journal_line_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dimension_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dimension_value_id')->constrained('dimension_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['journal_line_id', 'dimension_id']);
            $table->index('dimension_value_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_line_dimensions');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journals');
    }
};
