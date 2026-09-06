<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\AccountType;
use App\Enums\Accounting\ClosingBehavior;
use App\Enums\Accounting\NormalBalance;
use App\Enums\Accounting\StatementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'account_type' => AccountType::class,
        'normal_balance' => NormalBalance::class,
        'statement' => StatementType::class,
        'closing_behavior' => ClosingBehavior::class,
        'level' => 'integer',
        'is_posting' => 'boolean',
        'is_control' => 'boolean',
        'is_contra' => 'boolean',
        'is_statistical' => 'boolean',
        'requires_cost_center' => 'boolean',
        'requires_project' => 'boolean',
        'requires_branch' => 'boolean',
        'requires_department' => 'boolean',
        'is_bank_account' => 'boolean',
        'is_customer_subledger' => 'boolean',
        'is_supplier_subledger' => 'boolean',
        'is_asset_subledger' => 'boolean',
        'is_tax_account' => 'boolean',
        'is_intercompany' => 'boolean',
        'is_suspense' => 'boolean',
        'is_oci' => 'boolean',
        'eliminate_on_consolidation' => 'boolean',
        'reconciliation_required' => 'boolean',
        'opening_balance_allowed' => 'boolean',
        'manual_journal_allowed' => 'boolean',
        'system_generated_only' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Account, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /** @return HasMany<Account, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /** @return BelongsTo<Account, $this> */
    public function contraOf(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'contra_of_account_id');
    }

    /** @return HasMany<JournalLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /** True when this account may receive journal postings. */
    public function canPost(): bool
    {
        return $this->is_posting && $this->is_active && ! $this->is_statistical;
    }

    /** True when a manual (user-entered) journal may post to this account. */
    public function allowsManualPosting(): bool
    {
        return $this->canPost()
            && $this->manual_journal_allowed
            && ! $this->system_generated_only;
    }

    public function isEffectiveOn(\DateTimeInterface $date): bool
    {
        if ($this->effective_from && $date < $this->effective_from->startOfDay()) {
            return false;
        }

        if ($this->effective_to && $date > $this->effective_to->endOfDay()) {
            return false;
        }

        return true;
    }
}
