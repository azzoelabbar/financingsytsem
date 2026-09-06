<?php

declare(strict_types=1);

namespace App\Support\Api;

/**
 * Canonical dotted permissions for the HTTP/API application layer.
 */
final class ApiPermission
{
    public const AR_READ = 'ar.read';

    public const AR_WRITE = 'ar.write';

    public const AP_READ = 'ap.read';

    public const AP_WRITE = 'ap.write';

    public const GL_READ = 'gl.read';

    public const GL_WRITE = 'gl.write';

    public const REPORTS_READ = 'reports.read';

    public const INVESTMENTS_READ = 'investments.read';

    public const INVESTMENTS_WRITE = 'investments.write';

    public const EXPENSES_READ = 'expenses.read';

    public const EXPENSES_WRITE = 'expenses.write';

    public const PROJECTS_READ = 'projects.read';

    public const PROJECTS_WRITE = 'projects.write';

    public const TAX_READ = 'tax.read';

    public const TAX_WRITE = 'tax.write';

    public const OPENING_BALANCES_READ = 'opening_balances.read';

    public const OPENING_BALANCES_WRITE = 'opening_balances.write';

    public const BOOKS_READ = 'books.read';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::AR_READ,
            self::AR_WRITE,
            self::AP_READ,
            self::AP_WRITE,
            self::GL_READ,
            self::GL_WRITE,
            self::REPORTS_READ,
            self::INVESTMENTS_READ,
            self::INVESTMENTS_WRITE,
            self::EXPENSES_READ,
            self::EXPENSES_WRITE,
            self::PROJECTS_READ,
            self::PROJECTS_WRITE,
            self::TAX_READ,
            self::TAX_WRITE,
            self::OPENING_BALANCES_READ,
            self::OPENING_BALANCES_WRITE,
            self::BOOKS_READ,
        ];
    }
}
