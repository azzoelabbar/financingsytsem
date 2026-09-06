<?php

declare(strict_types=1);

namespace App\Services\Treasury\Exceptions;

class DuplicateDocumentException extends TreasuryException
{
    public static function make(string $type, string $number): self
    {
        return new self("A {$type} with number '{$number}' already exists for this company.");
    }
}
