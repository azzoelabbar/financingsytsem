<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Collections, Expected Credit Loss (IFRS 9) assessments, and bad-debt write-offs.
 * ECL methodology is configuration/input, never hard-coded. ECL and write-off
 * post through the Accounting Engine (journal_id links the resulting entry).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete();
            $table->date('activity_date');
            $table->string('type'); // call|email|promise|dunning|legal
            $table->unsignedTinyInteger('dunning_level')->nullable();
            $table->date('promise_to_pay_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'customer_id']);
        });

        Schema::create('ecl_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // null = portfolio-level
            $table->date('as_of_date');
            $table->unsignedTinyInteger('stage')->default(1); // IFRS 9 stages 1-3
            $table->decimal('gross_exposure', 28, 6)->default(0);
            $table->decimal('loss_rate', 9, 6)->nullable();
            $table->decimal('allowance_amount', 28, 6)->default(0);
            $table->string('method')->nullable(); // configurable methodology reference
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'as_of_date']);
        });

        Schema::create('bad_debt_writeoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete();
            $table->date('writeoff_date');
            $table->decimal('amount', 28, 6);
            $table->boolean('covered_by_allowance')->default(true);
            $table->string('reason')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bad_debt_writeoffs');
        Schema::dropIfExists('ecl_assessments');
        Schema::dropIfExists('collection_activities');
    }
};
