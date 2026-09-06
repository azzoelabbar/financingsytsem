<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Single-account install gate.
 *
 * This deployment is meant to hold exactly one account: the very first visitor
 * registers the owner account, and from that moment registration is closed for
 * good — only sign-in remains. Everything that shows or accepts a registration
 * must ask here first.
 *
 * The answer is read fresh every time (one indexed existence check, and only on
 * the landing, login and register routes) so it can never go stale mid-request.
 */
final class Registration
{
    /**
     * Registration is open only while the install has no account at all.
     */
    public static function isOpen(): bool
    {
        return ! User::query()->exists();
    }

    public static function isClosed(): bool
    {
        return ! self::isOpen();
    }
}
