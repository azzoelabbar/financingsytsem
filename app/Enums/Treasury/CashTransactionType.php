<?php

declare(strict_types=1);

namespace App\Enums\Treasury;

enum CashTransactionType: string
{
    case CASH_RECEIPT = 'cash_receipt';
    case CASH_PAYMENT = 'cash_payment';
    case BANK_RECEIPT = 'bank_receipt';
    case BANK_PAYMENT = 'bank_payment';
    case TRANSFER = 'transfer';
    case MISC_RECEIPT = 'misc_receipt';
    case MISC_PAYMENT = 'misc_payment';
    case BANK_FEE = 'bank_fee';
    case BANK_INTEREST = 'bank_interest';

    public function isInbound(): bool
    {
        return in_array($this, [
            self::CASH_RECEIPT,
            self::BANK_RECEIPT,
            self::MISC_RECEIPT,
            self::BANK_INTEREST,
        ], true);
    }

    public function isOutbound(): bool
    {
        return in_array($this, [
            self::CASH_PAYMENT,
            self::BANK_PAYMENT,
            self::MISC_PAYMENT,
            self::BANK_FEE,
            self::TRANSFER,
        ], true);
    }
}
