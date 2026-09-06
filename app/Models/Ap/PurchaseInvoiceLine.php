<?php

declare(strict_types=1);

namespace App\Models\Ap;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceLine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'line_no' => 'integer',
        'quantity' => 'decimal:6',
        'unit_price' => 'decimal:6',
        'net_amount' => 'decimal:6',
        'tax_amount' => 'decimal:6',
        'dimensions' => 'array',
    ];

    /** @return BelongsTo<PurchaseInvoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
