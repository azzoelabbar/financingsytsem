<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer receipts and their allocations against invoices. An allocation's
 * source is either a receipt OR a credit note (exactly one FK set) — modelled
 * relationally, not polymorphically (spec §47).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('number')->nullable();
            $table->date('receipt_date');
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->decimal('amount', 28, 6);
            $table->decimal('unallocated_amount', 28, 6)->default(0); // advance / overpayment
            $table->string('method')->default('bank'); // cash|bank|cheque|transfer
            $table->string('cash_bank_account_code'); // GL account debited (e.g. 110102)
            $table->string('status')->default('draft'); // draft|posted|reversed
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'customer_id', 'status']);
        });

        Schema::create('ar_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained('receipts')->cascadeOnDelete();
            $table->foreignId('sales_credit_note_id')->nullable()->constrained('sales_credit_notes')->cascadeOnDelete();
            $table->decimal('amount', 28, 6);
            $table->date('allocation_date');
            $table->timestamps();

            $table->index('sales_invoice_id');
            $table->index('receipt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_allocations');
        Schema::dropIfExists('receipts');
    }
};
