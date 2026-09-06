<?php

declare(strict_types=1);

namespace App\Models\Ar;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Settles part of a sales invoice from exactly one source: a receipt OR a credit
 * note (relational, not polymorphic — spec §47).
 */
class ArAllocation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:6',
        'fx_amount' => 'decimal:6',
        'allocation_date' => 'date',
    ];

    /** @return BelongsTo<SalesInvoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    /** @return BelongsTo<Receipt, $this> */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    /** @return BelongsTo<SalesCreditNote, $this> */
    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(SalesCreditNote::class, 'sales_credit_note_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function fxJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'fx_journal_id');
    }
}
