<?php

declare(strict_types=1);

namespace App\Models\Ap;

use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Settles part of a purchase invoice from exactly one source: a payment OR a
 * credit note (relational, not polymorphic).
 */
class ApAllocation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:6',
        'fx_amount' => 'decimal:6',
        'allocation_date' => 'date',
        'reversed_at' => 'datetime',
    ];

    /** @return BelongsTo<PurchaseInvoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    /** @return BelongsTo<SupplierPayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id');
    }

    /** @return BelongsTo<PurchaseCreditNote, $this> */
    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(PurchaseCreditNote::class, 'purchase_credit_note_id');
    }

    /** @return BelongsTo<Journal, $this> */
    public function fxJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'fx_journal_id');
    }
}
