<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AP source documents: purchase invoices, credit notes, debit notes (each with
 * lines). Posting links the document to its immutable GL journal (journal_id).
 * Tax is caller-supplied per line until the Tax Engine (Phase F).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $t->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $t->string('number')->nullable();
            $t->string('supplier_invoice_number')->nullable();
            $t->date('invoice_date');
            $t->date('document_date')->nullable();
            $t->date('due_date')->nullable();
            $t->string('currency', 3)->default('LYD');
            $t->decimal('exchange_rate', 20, 10)->default(1);
            $t->string('status')->default('draft');
            $t->decimal('net_total', 28, 6)->default(0);
            $t->decimal('tax_total', 28, 6)->default(0);
            $t->decimal('discount_total', 28, 6)->default(0);
            $t->decimal('gross_total', 28, 6)->default(0);
            $t->decimal('allocated_total', 28, 6)->default(0);
            $t->foreignId('payment_terms_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $t->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $t->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $t->string('reference')->nullable();
            $t->text('description')->nullable();
            $t->json('dimensions')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
            $t->unique(['company_id', 'supplier_id', 'supplier_invoice_number'], 'pi_supplier_invoice_unique');
            $t->index(['company_id', 'supplier_id', 'status']);
            $t->index(['company_id', 'due_date']);
            $t->index(['company_id', 'invoice_date']);
        });

        Schema::create('purchase_credit_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $t->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $t->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices')->nullOnDelete();
            $t->string('number')->nullable();
            $t->date('credit_note_date');
            $t->date('document_date')->nullable();
            $t->string('reason')->nullable();
            $t->string('currency', 3)->default('LYD');
            $t->decimal('exchange_rate', 20, 10)->default(1);
            $t->string('status')->default('draft');
            $t->decimal('net_total', 28, 6)->default(0);
            $t->decimal('tax_total', 28, 6)->default(0);
            $t->decimal('gross_total', 28, 6)->default(0);
            $t->decimal('allocated_total', 28, 6)->default(0);
            $t->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $t->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $t->string('reference')->nullable();
            $t->text('description')->nullable();
            $t->json('dimensions')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
            $t->index(['company_id', 'supplier_id', 'status']);
        });

        Schema::create('purchase_debit_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $t->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $t->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices')->nullOnDelete();
            $t->string('number')->nullable();
            $t->date('debit_note_date');
            $t->date('document_date')->nullable();
            $t->string('reason')->nullable();
            $t->string('currency', 3)->default('LYD');
            $t->decimal('exchange_rate', 20, 10)->default(1);
            $t->string('status')->default('draft');
            $t->decimal('net_total', 28, 6)->default(0);
            $t->decimal('tax_total', 28, 6)->default(0);
            $t->decimal('gross_total', 28, 6)->default(0);
            $t->decimal('allocated_total', 28, 6)->default(0);
            $t->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $t->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $t->string('reference')->nullable();
            $t->text('description')->nullable();
            $t->json('dimensions')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
            $t->index(['company_id', 'supplier_id', 'status']);
        });

        Schema::create('purchase_invoice_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
            $t->unsignedInteger('line_no');
            $t->string('description')->nullable();
            $t->string('expense_account_code');
            $t->string('item_ref')->nullable();
            $t->decimal('quantity', 28, 6)->default(1);
            $t->decimal('unit_price', 28, 6)->default(0);
            $t->decimal('net_amount', 28, 6)->default(0);
            $t->string('tax_code')->nullable();
            $t->string('tax_account_code')->nullable();
            $t->decimal('tax_amount', 28, 6)->default(0);
            $t->json('dimensions')->nullable();
            $t->timestamps();
            $t->index(['purchase_invoice_id', 'line_no']);
        });

        Schema::create('purchase_credit_note_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_credit_note_id')->constrained('purchase_credit_notes')->cascadeOnDelete();
            $t->unsignedInteger('line_no');
            $t->string('description')->nullable();
            $t->string('expense_account_code');
            $t->string('item_ref')->nullable();
            $t->decimal('quantity', 28, 6)->default(1);
            $t->decimal('unit_price', 28, 6)->default(0);
            $t->decimal('net_amount', 28, 6)->default(0);
            $t->string('tax_code')->nullable();
            $t->string('tax_account_code')->nullable();
            $t->decimal('tax_amount', 28, 6)->default(0);
            $t->json('dimensions')->nullable();
            $t->timestamps();
            $t->index(['purchase_credit_note_id', 'line_no']);
        });

        Schema::create('purchase_debit_note_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_debit_note_id')->constrained('purchase_debit_notes')->cascadeOnDelete();
            $t->unsignedInteger('line_no');
            $t->string('description')->nullable();
            $t->string('expense_account_code');
            $t->string('item_ref')->nullable();
            $t->decimal('quantity', 28, 6)->default(1);
            $t->decimal('unit_price', 28, 6)->default(0);
            $t->decimal('net_amount', 28, 6)->default(0);
            $t->string('tax_code')->nullable();
            $t->string('tax_account_code')->nullable();
            $t->decimal('tax_amount', 28, 6)->default(0);
            $t->json('dimensions')->nullable();
            $t->timestamps();
            $t->index(['purchase_debit_note_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_debit_note_lines');
        Schema::dropIfExists('purchase_credit_note_lines');
        Schema::dropIfExists('purchase_invoice_lines');
        Schema::dropIfExists('purchase_debit_notes');
        Schema::dropIfExists('purchase_credit_notes');
        Schema::dropIfExists('purchase_invoices');
    }
};
