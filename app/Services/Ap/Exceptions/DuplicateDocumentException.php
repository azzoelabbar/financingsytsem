<?php

declare(strict_types=1);

namespace App\Services\Ap\Exceptions;

class DuplicateDocumentException extends ApException
{
    public static function make(string $type, string $number): self
    {
        return new self("A {$type} with number '{$number}' already exists for this company.");
    }

    public static function supplierInvoice(string $number): self
    {
        return new self("Supplier invoice number '{$number}' already exists for this supplier.");
    }
}
