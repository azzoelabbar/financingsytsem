<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Country Localization Layer (spec §23, §62). Legal/tax/accounting rules live
 * here as versioned, effective-dated, audited DATA — never hard-coded in the
 * accounting core. A change of law is a new version, not an edit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localization_rules', function (Blueprint $table) {
            $table->id();
            $table->string('country', 2);              // ISO 3166-1 alpha-2 (LY, AE, SA, …)
            $table->string('rule_code');               // e.g. LY_VAT_STANDARD
            $table->string('rule_type');               // tax_rate|threshold|deduction|contribution|legal_limit|reporting_deadline|document_requirement|numbering_rule|withholding
            $table->string('name_ar');
            $table->string('name_en')->nullable();

            // Values are nullable: a rule may be defined (catalogued) before its
            // legal value is sourced. Nothing here is assumed.
            $table->decimal('rate', 20, 10)->nullable();
            $table->decimal('amount', 28, 6)->nullable();
            $table->text('formula')->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('meta')->nullable();

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->string('authority');               // issuing authority
            $table->string('legal_reference');         // law/decree/circular reference
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft'); // draft|pending_approval|active|superseded|repealed
            $table->string('source_url')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['country', 'rule_code', 'version']);
            $table->index(['country', 'rule_code', 'status', 'effective_from'], 'loc_rule_resolve_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localization_rules');
    }
};
