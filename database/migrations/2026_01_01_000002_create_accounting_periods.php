<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fiscal calendar with a strong period engine (spec §26). Posting is only
 * allowed into OPEN periods; SOFT_CLOSED requires an override permission;
 * HARD_CLOSED is immutable. Any reopen must be recorded in the audit log.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // e.g. "2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open'); // open|soft_closed|hard_closed
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_no'); // 1..12 (13+ for adjustment periods)
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_adjustment')->default(false);
            $table->string('status')->default('open'); // open|soft_closed|hard_closed
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'period_no']);
            $table->index(['company_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_periods');
        Schema::dropIfExists('fiscal_years');
    }
};
