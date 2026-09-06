<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Phases T–Z: investments, expenses, projects, opening balances, statutory runs. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('classification'); // fvtpl|fvoci|amortized
            $table->string('gl_account_code');
            $table->decimal('cost', 28, 6);
            $table->decimal('carrying', 28, 6);
            $table->string('status')->default('active');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('number');
            $table->string('employee_ref');
            $table->string('expense_account_code');
            $table->string('payable_account_code');
            $table->decimal('amount', 28, 6);
            $table->string('status')->default('draft'); // posted|reimbursed
            $table->date('claim_date');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'number']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('status')->default('open');
            $table->decimal('charged', 28, 6)->default(0);
            $table->decimal('capitalized', 28, 6)->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('project_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('type'); // charge|capitalize
            $table->date('cost_date');
            $table->decimal('amount', 28, 6);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('opening_balance_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->date('as_of');
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('localization_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('country', 8);
            $table->string('rule_code');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['country', 'rule_code']);
        });

        Schema::create('statutory_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('country', 8);
            $table->string('kind');
            $table->string('period_key');
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statutory_returns');
        Schema::dropIfExists('localization_sequences');
        Schema::dropIfExists('opening_balance_runs');
        Schema::dropIfExists('project_costs');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('expense_claims');
        Schema::dropIfExists('investments');
    }
};
