<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AR source documents: sales invoices, credit notes, debit notes (each with
 * lines). Posting links the document to its immutable GL journal (journal_id);
 * a posted document is corrected by credit note / reversal, never edited/deleted.
 * Tax is carried explicitly per line (caller-supplied until the Tax Engine, Phase F).
 * Columns are inlined per table so static analysis resolves model properties.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->string('number')->nullable();
            $t->date('invoice_date');
            $t->date('document_date')->nullable();
            $t->date('due_date')->nullable();
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
            $t->text('notes')->nullable();
            $t->boolean('is_recurring')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
            $t->index(['company_id', 'customer_id', 'status']);
        });

        Schema::create('sales_credit_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete();
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
            $t->text('notes')->nullable();
            $t->boolean('is_recurring')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
            $t->index(['company_id', 'customer_id', 'status']);
        });

        Schema::create('sales_debit_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete();
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
            $t->text('notes')->nullable();
            $t->boolean('is_recurring')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
            $t->index(['company_id', 'customer_id', 'status']);
        });

        Schema::create('sales_invoice_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $t->unsignedInteger('line_no');
            $t->string('description')->nullable();
            $t->string('revenue_account_code');
            $t->string('item_ref')->nullable();
            $t->decimal('quantity', 28, 6)->default(1);
            $t->decimal('unit_price', 28, 6)->default(0);
            $t->decimal('net_amount', 28, 6)->default(0);
            $t->string('tax_code')->nullable();
            $t->string('tax_account_code')->nullable();
            $t->decimal('tax_amount', 28, 6)->default(0);
            $t->json('dimensions')->nullable();
            $t->timestamps();
            $t->index(['sales_invoice_id', 'line_no']);
        });

        Schema::create('sales_credit_note_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sales_credit_note_id')->constrained('sales_credit_notes')->cascadeOnDelete();
            $t->unsignedInteger('line_no');
            $t->string('description')->nullable();
            $t->string('revenue_account_code');
            $t->string('item_ref')->nullable();
            $t->decimal('quantity', 28, 6)->default(1);
            $t->decimal('unit_price', 28, 6)->default(0);
            $t->decimal('net_amount', 28, 6)->default(0);
            $t->string('tax_code')->nullable();
            $t->string('tax_account_code')->nullable();
            $t->decimal('tax_amount', 28, 6)->default(0);
            $t->json('dimensions')->nullable();
            $t->timestamps();
            $t->index(['sales_credit_note_id', 'line_no']);
        });

        Schema::create('sales_debit_note_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sales_debit_note_id')->constrained('sales_debit_notes')->cascadeOnDelete();
            $t->unsignedInteger('line_no');
            $t->string('description')->nullable();
            $t->string('revenue_account_code');
            $t->string('item_ref')->nullable();
            $t->decimal('quantity', 28, 6)->default(1);
            $t->decimal('unit_price', 28, 6)->default(0);
            $t->decimal('net_amount', 28, 6)->default(0);
            $t->string('tax_code')->nullable();
            $t->string('tax_account_code')->nullable();
            $t->decimal('tax_amount', 28, 6)->default(0);
            $t->json('dimensions')->nullable();
            $t->timestamps();
            $t->index(['sales_debit_note_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_debit_note_lines');
        Schema::dropIfExists('sales_credit_note_lines');
        Schema::dropIfExists('sales_invoice_lines');
        Schema::dropIfExists('sales_debit_notes');
        Schema::dropIfExists('sales_credit_notes');
        Schema::dropIfExists('sales_invoices');
    }
};
