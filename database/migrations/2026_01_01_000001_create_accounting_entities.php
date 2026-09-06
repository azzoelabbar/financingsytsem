<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-tenant organizational backbone (spec §7, §51):
 *   Organization (tenant) → Company (legal entity) → Books / Currencies.
 * Branches, cost centres, projects etc. are modelled as generic dimensions
 * (see the dimensions migration) so the number of dimensions is not fixed
 * inside the accounting core (spec §38).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('default_currency', 3)->default('LYD');
            $table->string('country', 2)->default('LY');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            // Functional = the currency the entity actually operates in (IAS 21).
            // Presentation = the currency financial statements are shown in.
            $table->string('functional_currency', 3)->default('LYD');
            $table->string('presentation_currency', 3)->default('LYD');
            $table->string('country', 2)->default('LY');
            // full_ifrs | ifrs_for_smes | local_gaap (spec §6)
            $table->string('accounting_framework')->default('local_gaap');
            $table->foreignId('parent_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('tax_registration_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->string('code', 3)->primary(); // ISO 4217
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('symbol', 8)->nullable();
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->string('rate_type')->default('spot'); // spot|closing|average|historical
            $table->date('rate_date');
            $table->decimal('rate', 20, 10);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'from_currency', 'to_currency', 'rate_type', 'rate_date'], 'exch_rate_unique');
            $table->index(['company_id', 'from_currency', 'to_currency', 'rate_date']);
        });

        Schema::create('accounting_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('basis')->default('local'); // local|ifrs|tax (spec §40)
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_books');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('organizations');
    }
};
