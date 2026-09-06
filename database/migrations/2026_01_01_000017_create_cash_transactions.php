<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cash & bank transactions (Phase C). Every posted row links an immutable
 * journal_id produced via the Accounting Engine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('treasury_account_id')->constrained('treasury_accounts')->restrictOnDelete();
            $table->foreignId('counter_treasury_account_id')->nullable()->constrained('treasury_accounts')->nullOnDelete();
            $table->string('number')->nullable();
            $table->string('type'); // cash_receipt|cash_payment|bank_receipt|bank_payment|transfer|misc_receipt|misc_payment|bank_fee|bank_interest
            $table->date('transaction_date');
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->decimal('amount', 28, 6);
            $table->string('direction'); // in | out
            $table->string('counter_account_code')->nullable(); // P&L / clearing for misc / fee / interest
            $table->string('reference')->nullable();
            $table->string('cheque_number')->nullable();
            $table->date('value_date')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->boolean('is_cleared')->default(false);
            $table->date('cleared_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'treasury_account_id', 'status']);
            $table->index(['company_id', 'transaction_date']);
            $table->index(['treasury_account_id', 'is_cleared']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
