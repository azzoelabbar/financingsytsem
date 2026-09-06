<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive metadata for the 330-account enterprise chart (canonical). Only ADDS
 * columns — the 168 legacy chart and its seeder are untouched.
 *   - subledger_mapping : AR|AP|INV|FA|BANK|TAX|PAYROLL|LEASE (which subledger backs the account)
 *   - is_intercompany   : balance eliminated on consolidation
 *   - is_suspense       : suspense/clearing account (must not carry an unexplained balance)
 *   - is_oci            : other-comprehensive-income reserve
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('subledger_mapping')->nullable()->after('is_asset_subledger');
            $table->boolean('is_intercompany')->default(false)->after('is_tax_account');
            $table->boolean('is_suspense')->default(false)->after('is_intercompany');
            $table->boolean('is_oci')->default(false)->after('is_suspense');
            $table->boolean('eliminate_on_consolidation')->default(false)->after('is_intercompany');

            $table->index(['company_id', 'subledger_mapping']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'subledger_mapping']);
            $table->dropColumn(['subledger_mapping', 'is_intercompany', 'is_suspense', 'is_oci', 'eliminate_on_consolidation']);
        });
    }
};
