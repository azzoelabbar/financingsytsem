<?php

declare(strict_types=1);

namespace App\Services\Ar\Exceptions;

class DuplicateDocumentException extends ArException
{
    public static function make(string $type, string $number): self
    {
        return new self("A {$type} with number '{$number}' already exists for this company.");
    }
}
