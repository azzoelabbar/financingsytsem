<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase E — FX: revaluation runs, settlement FX links, and document revaluation rates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->decimal('revaluation_rate', 20, 10)->nullable()->after('exchange_rate');
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->decimal('revaluation_rate', 20, 10)->nullable()->after('exchange_rate');
        });

        Schema::table('ar_allocations', function (Blueprint $table) {
            $table->foreignId('fx_journal_id')->nullable()->after('amount')->constrained('journals')->nullOnDelete();
            $table->decimal('fx_amount', 28, 6)->nullable()->after('fx_journal_id');
        });

        Schema::table('ap_allocations', function (Blueprint $table) {
            $table->foreignId('fx_journal_id')->nullable()->after('amount')->constrained('journals')->nullOnDelete();
            $table->decimal('fx_amount', 28, 6)->nullable()->after('fx_journal_id');
        });

        Schema::create('fx_revaluation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->date('as_of_date');
            $table->string('status')->default('posted'); // posted|reversed
            $table->decimal('total_gain', 28, 6)->default(0);
            $table->decimal('total_loss', 28, 6)->default(0);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'as_of_date']);
        });

        Schema::create('fx_revaluation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fx_revaluation_run_id')->constrained('fx_revaluation_runs')->cascadeOnDelete();
            $table->string('side'); // ar|ap
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->string('currency', 3);
            $table->decimal('fc_amount', 28, 6);
            $table->decimal('historical_rate', 20, 10);
            $table->decimal('closing_rate', 20, 10);
            $table->decimal('historical_functional', 28, 6);
            $table->decimal('closing_functional', 28, 6);
            $table->decimal('difference', 28, 6);
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });

        Schema::create('fx_translation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->date('as_of_date');
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('closing_rate', 20, 10);
            $table->decimal('net_assets_functional', 28, 6);
            $table->decimal('translated_amount', 28, 6);
            $table->decimal('difference', 28, 6)->default(0);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_translation_runs');
        Schema::dropIfExists('fx_revaluation_lines');
        Schema::dropIfExists('fx_revaluation_runs');

        Schema::table('ap_allocations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fx_journal_id');
            $table->dropColumn('fx_amount');
        });

        Schema::table('ar_allocations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fx_journal_id');
            $table->dropColumn('fx_amount');
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn('revaluation_rate');
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn('revaluation_rate');
        });
    }
};
