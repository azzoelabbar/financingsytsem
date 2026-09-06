<?php

declare(strict_types=1);

namespace App\Support\Accounting;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Security\AccessGrant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;

/**
 * Session-backed company/book context for Blade/Livewire UI.
 */
final class AccountingContext
{
    public const SESSION_COMPANY = 'erp.company_id';

    public const SESSION_BOOK = 'erp.book_id';

    /**
     * @return EloquentCollection<int, Company>
     */
    public function companiesFor(User $user): EloquentCollection
    {
        $ids = AccessGrant::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('company_id');

        return Company::query()->whereIn('id', $ids)->orderBy('code')->get();
    }

    /**
     * @return list<string>
     */
    public function permissions(User $user, Company $company): array
    {
        /** @var list<string> $permissions */
        $permissions = AccessGrant::query()
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->pluck('permission')
            ->all();

        return $permissions;
    }

    public function can(string $permission): bool
    {
        $user = Auth::user();
        $company = $this->company();
        if ($user === null || $company === null) {
            return false;
        }

        return in_array($permission, $this->permissions($user, $company), true);
    }

    public function company(): ?Company
    {
        $user = Auth::user();
        if ($user === null) {
            return null;
        }

        $companies = $this->companiesFor($user);
        if ($companies->isEmpty()) {
            return null;
        }

        $sessionCompanyId = (int) session(self::SESSION_COMPANY);
        $company = $companies->first(fn (Company $candidate): bool => $candidate->id === $sessionCompanyId)
            ?? $companies->first();

        if ($sessionCompanyId !== $company->id) {
            session([self::SESSION_COMPANY => $company->id]);
        }

        return $company;
    }

    public function book(): ?AccountingBook
    {
        $company = $this->company();
        if ($company === null) {
            return null;
        }

        /** @var EloquentCollection<int, AccountingBook> $books */
        $books = AccountingBook::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $sessionBookId = (int) session(self::SESSION_BOOK);
        $book = $books->first(fn (AccountingBook $candidate): bool => $candidate->id === $sessionBookId)
            ?? $books->first(fn (AccountingBook $candidate): bool => $candidate->code === 'LOCAL')
            ?? $books->first(fn (AccountingBook $candidate): bool => (bool) $candidate->is_primary)
            ?? $books->first();

        if ($book === null) {
            return null;
        }

        if ($sessionBookId !== $book->id) {
            session([self::SESSION_BOOK => $book->id]);
        }

        return $book;
    }

    public function period(): ?FiscalPeriod
    {
        $company = $this->company();
        if ($company === null) {
            return null;
        }

        return FiscalPeriod::query()
            ->where('company_id', $company->id)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->first();
    }

    public function setCompany(int $companyId): void
    {
        session([self::SESSION_COMPANY => $companyId]);
        session()->forget(self::SESSION_BOOK);
    }

    public function setBook(int $bookId): void
    {
        session([self::SESSION_BOOK => $bookId]);
    }
}
