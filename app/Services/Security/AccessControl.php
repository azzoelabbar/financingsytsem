<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\Accounting\Company;
use App\Models\Security\AccessGrant;
use App\Models\User;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Auth\Access\AuthorizationException;

class AccessControl
{
    public function grant(User $user, Company $company, string $permission): AccessGrant
    {
        return AccessGrant::query()->firstOrCreate([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'permission' => $permission,
        ]);
    }

    public function allows(User $user, Company $company, string $permission): bool
    {
        return AccessGrant::query()
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->where('permission', $permission)
            ->exists();
    }

    public function belongsToCompany(User $user, Company $company): bool
    {
        return AccessGrant::query()
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->exists();
    }

    /**
     * HTTP-layer authorization: always requires an authenticated actor (no null bypass).
     *
     * @throws AuthorizationException
     */
    public function authorize(User $user, Company $company, string $permission): void
    {
        if (! $this->allows($user, $company, $permission)) {
            throw new AuthorizationException("Missing permission '{$permission}' for this company.");
        }
    }

    public function assert(?User $actor, Company $company, string $permission): void
    {
        if ($actor === null) {
            return;
        }
        if (! $this->allows($actor, $company, $permission)) {
            throw new PostingException("Unauthorized: missing permission '{$permission}'.");
        }
    }

    public function assertNotSelf(int $createdBy, User $actor): void
    {
        if ($createdBy === $actor->id) {
            throw new PostingException('SoD-1: actor cannot approve or post a journal they created.');
        }
    }
}
