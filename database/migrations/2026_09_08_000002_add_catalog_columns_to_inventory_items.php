<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->string('category')->nullable()->after('name');
            $table->string('unit')->nullable()->after('category');
            $table->decimal('standard_cost', 28, 6)->nullable()->after('cogs_account_code');
            $table->decimal('sale_price', 28, 6)->nullable()->after('standard_cost');
            $table->decimal('reorder_level', 28, 6)->nullable()->after('sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropColumn(['category', 'unit', 'standard_cost', 'sale_price', 'reorder_level']);
        });
    }
};
