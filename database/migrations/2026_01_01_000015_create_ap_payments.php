<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supplier payments and their allocations against purchase invoices. An
 * allocation's source is either a payment OR a credit note (exactly one FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('number')->nullable();
            $table->date('payment_date');
            $table->string('currency', 3)->default('LYD');
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->decimal('amount', 28, 6);
            $table->decimal('unallocated_amount', 28, 6)->default(0);
            $table->string('method')->default('bank');
            $table->string('cash_bank_account_code');
            $table->string('status')->default('draft');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'supplier_id', 'status']);
            $table->index(['company_id', 'payment_date']);
        });

        Schema::create('ap_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
            $table->foreignId('supplier_payment_id')->nullable()->constrained('supplier_payments')->cascadeOnDelete();
            $table->foreignId('purchase_credit_note_id')->nullable()->constrained('purchase_credit_notes')->cascadeOnDelete();
            $table->decimal('amount', 28, 6);
            $table->date('allocation_date');
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->index('purchase_invoice_id');
            $table->index('supplier_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_allocations');
        Schema::dropIfExists('supplier_payments');
    }
};
