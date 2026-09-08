<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spreadsheet_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('book_id')->constrained('accounting_books');
            $table->foreignId('user_id')->constrained('users');
            $table->string('kind', 30);
            $table->string('filename');
            $table->string('fingerprint', 64);
            $table->json('payload');
            $table->json('options');
            $table->json('results')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'book_id', 'kind']);
        });
        Schema::create('spreadsheet_import_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('import_id')->constrained('spreadsheet_imports');
            $table->string('kind', 30);
            $table->string('source_key', 64);
            $table->unique(['company_id', 'kind', 'source_key'], 'import_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spreadsheet_import_keys');
        Schema::dropIfExists('spreadsheet_imports');
    }
};
