<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dimension Framework (spec §38). Branch / Cost Center / Profit Center / Project
 * / Department / Location / Segment etc. are NOT hard-coded columns. They are
 * configurable dimensions, and journal lines attach dimension values through a
 * pivot so new analytical dimensions can be added without touching the core.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // BRANCH, COST_CENTER, PROJECT, DEPARTMENT, ...
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->boolean('is_required_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('dimension_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('dimension_values')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['dimension_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimension_values');
        Schema::dropIfExists('dimensions');
    }
};
