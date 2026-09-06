<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('account_code');
            $table->timestamps();
            $table->unique(['company_id', 'role']);
        });

        Schema::table('investments', function (Blueprint $table) {
            $table->string('investment_number')->nullable()->after('code');
            $table->string('instrument_type')->default('equity')->after('name');
            $table->string('currency', 3)->nullable()->after('classification');
            $table->decimal('quantity', 28, 6)->default(1)->after('currency');
            $table->decimal('unit_cost', 28, 6)->default(0)->after('quantity');
            $table->decimal('acquisition_cost', 28, 6)->default(0)->after('unit_cost');
            $table->decimal('carrying_amount', 28, 6)->default(0)->after('acquisition_cost');
            $table->decimal('fair_value', 28, 6)->nullable()->after('carrying_amount');
            $table->decimal('effective_interest_rate', 12, 6)->nullable()->after('fair_value');
            $table->date('acquired_at')->nullable()->after('status');
            $table->date('maturity_at')->nullable()->after('acquired_at');
            $table->json('dimensions')->nullable()->after('maturity_at');
        });

        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->string('type');
            $table->date('transacted_at');
            $table->decimal('amount', 28, 6);
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 18, 10)->default(1);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('investment_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->date('valued_at');
            $table->decimal('fair_value', 28, 6);
            $table->decimal('movement', 28, 6);
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('investment_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->string('kind'); // dividend|interest
            $table->date('income_date');
            $table->decimal('amount', 28, 6);
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('investment_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->date('disposed_at');
            $table->decimal('proceeds', 28, 6);
            $table->decimal('carrying_amount', 28, 6);
            $table->decimal('gain_loss', 28, 6);
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('expense_claims', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('book_id');
            $table->string('claim_number')->nullable()->after('number');
            $table->string('currency', 3)->nullable()->after('claim_date');
            $table->decimal('total_amount', 28, 6)->default(0)->after('amount');
            $table->decimal('reimbursed_amount', 28, 6)->default(0)->after('total_amount');
            $table->text('description')->nullable();
            $table->json('dimensions')->nullable();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('expense_account_code');
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('expense_claim_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_claim_id')->constrained('expense_claims')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->string('expense_account_code');
            $table->decimal('amount', 28, 6);
            $table->decimal('tax_amount', 28, 6)->default(0);
            $table->string('tax_code')->nullable();
            $table->string('tax_account_code')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->json('dimensions')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_claim_id')->constrained('expense_claims')->cascadeOnDelete();
            $table->string('decision'); // approved|rejected
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_claim_id')->constrained('expense_claims')->cascadeOnDelete();
            $table->date('paid_on');
            $table->decimal('amount', 28, 6);
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->string('status')->default('posted');
            $table->timestamps();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_number')->nullable()->after('code');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 28, 6)->default(0);
            $table->string('currency', 3)->nullable();
            $table->json('dimensions')->nullable();
        });

        Schema::table('project_costs', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('type');
            $table->string('source_ref')->nullable()->after('source');
            $table->decimal('capitalized_amount', 28, 6)->default(0)->after('amount');
            $table->json('dimensions')->nullable();
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->date('due_on')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('project_capitalizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_cost_id')->constrained('project_costs')->cascadeOnDelete();
            $table->date('capitalized_on');
            $table->decimal('amount', 28, 6);
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_cost_id', 'journal_id']);
        });

        Schema::create('opening_balance_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->date('as_of');
            $table->string('status')->default('draft');
            $table->string('currency', 3);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'book_id', 'as_of']);
        });

        Schema::create('opening_balance_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opening_balance_batch_id')->constrained('opening_balance_batches')->cascadeOnDelete();
            $table->string('account_code');
            $table->decimal('debit', 28, 6)->default(0);
            $table->decimal('credit', 28, 6)->default(0);
            $table->string('currency', 3)->nullable();
            $table->decimal('exchange_rate', 18, 10)->default(1);
            $table->json('dimensions')->nullable();
            $table->timestamps();
        });

        Schema::create('book_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('source_account_code');
            $table->string('target_account_code');
            $table->timestamps();
            $table->unique(['book_id', 'source_account_code'], 'book_acct_map_unique');
        });

        Schema::create('book_posting_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('accounting_books')->cascadeOnDelete();
            $table->string('source_type');
            $table->decimal('amount_factor', 18, 10)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['book_id', 'source_type'], 'book_post_rule_unique');
        });

        Schema::create('legal_invoice_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('country', 8);
            $table->string('document_type');
            $table->string('period_key')->nullable();
            $table->string('number');
            $table->string('legal_reference')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'document_type', 'number'], 'legal_inv_num_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_invoice_numbers');
        Schema::dropIfExists('book_posting_rules');
        Schema::dropIfExists('book_account_mappings');
        Schema::dropIfExists('opening_balance_lines');
        Schema::dropIfExists('opening_balance_batches');
        Schema::dropIfExists('project_capitalizations');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('expense_reimbursements');
        Schema::dropIfExists('expense_approvals');
        Schema::dropIfExists('expense_claim_lines');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('investment_disposals');
        Schema::dropIfExists('investment_incomes');
        Schema::dropIfExists('investment_valuations');
        Schema::dropIfExists('investment_transactions');
        Schema::dropIfExists('account_roles');
    }
};
