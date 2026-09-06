<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts Payable master data (Phase B). Suppliers are the AP subledger;
 * the General Ledger holds only the AP control account (resolved from the
 * chart via is_control + subledger_mapping=AP — 210101 on the enterprise chart).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // supplier number
            $table->string('legal_name');
            $table->string('trading_name')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tax_registration')->nullable();
            $table->string('currency', 3)->default('LYD');
            $table->string('ap_control_code'); // resolved from chart, not a global constant
            $table->string('default_expense_account_code')->nullable();
            $table->string('default_inventory_account_code')->nullable();
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->decimal('credit_limit', 28, 6)->nullable();
            $table->json('dimensions')->nullable();
            $table->string('status')->default('active'); // active|inactive|blocked
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('supplier_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('billing');
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('LY');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('supplier_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_name')->nullable();
            $table->string('account_number');
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('currency', 3)->default('LYD');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('supplier_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('tax_type')->default('vat');
            $table->string('tax_number');
            $table->string('country', 2)->default('LY');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_tax_profiles');
        Schema::dropIfExists('supplier_bank_accounts');
        Schema::dropIfExists('supplier_addresses');
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('suppliers');
    }
};
