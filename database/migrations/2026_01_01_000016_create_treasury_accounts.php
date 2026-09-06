<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Treasury master (Phase C): banks and cash/bank accounts linked to GL codes
 * from the chart (never inventing accounts). Book balances derive from posted
 * cash transactions and must reconcile to the GL (INV-6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('swift')->nullable();
            $table->string('country', 2)->default('LY');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('treasury_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('code');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('type'); // cash | bank
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('currency', 3)->default('LYD');
            $table->string('gl_account_code'); // chart posting account (e.g. 110101 / 110102)
            $table->decimal('opening_balance', 28, 6)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->string('status')->default('active'); // active | inactive | closed
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'gl_account_code']);
            $table->index(['company_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_accounts');
        Schema::dropIfExists('banks');
    }
};
