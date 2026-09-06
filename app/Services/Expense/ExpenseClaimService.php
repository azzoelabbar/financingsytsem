<?php

declare(strict_types=1);

namespace App\Services\Expense;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Expense\ExpenseClaim;
use App\Models\User;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Tax\TaxEngine;
use Illuminate\Support\Facades\DB;

class ExpenseClaimService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly AccountRoleResolver $roles,
        private readonly ControlAccountResolver $controls,
        private readonly TaxEngine $tax,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function createDraft(Company $company, AccountingBook $book, array $header, array $lines): ExpenseClaim
    {
        $number = is_string($header['number'] ?? $header['claim_number'] ?? null)
            ? (string) ($header['number'] ?? $header['claim_number'])
            : throw new PostingException('Claim number is required.');
        if (ExpenseClaim::query()->where('company_id', $company->id)->where('number', $number)->exists()) {
            throw new PostingException("Duplicate expense claim '{$number}'.");
        }
        if ($lines === []) {
            throw new PostingException('An expense claim must have at least one line.');
        }
        $date = is_string($header['claim_date'] ?? null) ? $header['claim_date'] : now()->toDateString();
        $currency = is_string($header['currency'] ?? null) ? $header['currency'] : (string) $company->functional_currency;
        $payable = $this->roles->code($company, 'expense.employee_payable');

        return DB::transaction(function () use ($company, $book, $header, $lines, $number, $date, $currency, $payable): ExpenseClaim {
            $claim = ExpenseClaim::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'employee_id' => $header['employee_id'] ?? null,
                'number' => $number,
                'claim_number' => $number,
                'employee_ref' => $header['employee_ref'] ?? (string) ($header['employee_id'] ?? 'staff'),
                'expense_account_code' => $this->roles->code($company, 'expense.default'),
                'payable_account_code' => $payable,
                'claim_date' => $date,
                'currency' => $currency,
                'status' => 'draft',
                'description' => $header['description'] ?? null,
                'dimensions' => is_array($header['dimensions'] ?? null) ? $header['dimensions'] : null,
                'amount' => '0',
                'total_amount' => '0',
            ]);
            $total = '0';
            $lineNo = 0;
            foreach ($lines as $line) {
                $lineNo++;
                $net = Decimal::of(is_scalar($line['amount'] ?? $line['net'] ?? null) ? (string) ($line['amount'] ?? $line['net']) : '0');
                if (! Decimal::isPositive($net)) {
                    throw new PostingException("Expense line {$lineNo} amount must be positive.");
                }
                $account = is_string($line['expense_account'] ?? $line['expense_account_code'] ?? null)
                    ? (string) ($line['expense_account'] ?? $line['expense_account_code'])
                    : $this->roles->code($company, 'expense.default');
                $this->controls->assertPostingAccount($company, $account);
                $taxAmt = '0';
                $taxAccount = null;
                $taxCode = is_string($line['tax_code'] ?? null) ? $line['tax_code'] : null;
                if ($taxCode !== null) {
                    $computed = $this->tax->compute($company, $taxCode, $net, $date);
                    $taxAmt = $computed['tax'];
                    $taxAccount = $computed['gl_account'];
                } elseif (isset($line['tax']) && is_scalar($line['tax'])) {
                    $taxAmt = Decimal::of((string) $line['tax']);
                    $taxAccount = is_string($line['tax_account'] ?? null) ? $line['tax_account'] : $this->roles->code($company, 'tax.input_vat');
                }
                $claim->lines()->create([
                    'line_no' => $lineNo,
                    'expense_account_code' => $account,
                    'amount' => $net,
                    'tax_amount' => $taxAmt,
                    'tax_code' => $taxCode,
                    'tax_account_code' => $taxAccount,
                    'description' => $line['description'] ?? null,
                    'project_id' => $line['project_id'] ?? null,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                    'dimensions' => is_array($line['dimensions'] ?? null) ? $line['dimensions'] : ($header['dimensions'] ?? null),
                ]);
                $total = Decimal::add($total, Decimal::add($net, $taxAmt));
            }
            $claim->forceFill(['amount' => $total, 'total_amount' => $total])->save();
            $this->audit->record($claim, 'created', $company->id, null, ['number' => $number]);

            return $claim->fresh(['lines']) ?? $claim;
        });
    }

    public function submit(ExpenseClaim $claim): ExpenseClaim
    {
        if ($claim->status !== 'draft') {
            throw new PostingException('Only draft claims can be submitted.');
        }
        $claim->forceFill(['status' => 'submitted'])->save();
        $this->audit->record($claim, 'submitted', $claim->company_id);

        return $claim;
    }

    public function approve(ExpenseClaim $claim, ?User $actor = null): ExpenseClaim
    {
        if ($claim->status !== 'submitted') {
            throw new PostingException('Only submitted claims can be approved.');
        }
        $claim->approvals()->create(['decision' => 'approved', 'actor_id' => $actor?->id]);
        $claim->forceFill(['status' => 'approved'])->save();
        $this->audit->record($claim, 'approved', $claim->company_id);

        return $claim;
    }

    public function reject(ExpenseClaim $claim, ?User $actor = null, ?string $reason = null): ExpenseClaim
    {
        if (! in_array($claim->status, ['draft', 'submitted'], true)) {
            throw new PostingException('Posted claims cannot be rejected.');
        }
        $claim->approvals()->create(['decision' => 'rejected', 'actor_id' => $actor?->id, 'reason' => $reason]);
        $claim->forceFill(['status' => 'rejected'])->save();
        $this->audit->record($claim, 'rejected', $claim->company_id, null, ['reason' => $reason]);

        return $claim;
    }

    public function post(ExpenseClaim $claim, bool $allowUnapproved = false): ExpenseClaim
    {
        if ($claim->status === 'rejected') {
            throw new PostingException('A rejected expense must not create accounting.');
        }
        if ($claim->status === 'posted' || $claim->status === 'reimbursed') {
            throw new PostingException('Claim is already posted.');
        }
        if ($claim->status !== 'approved' && ! $allowUnapproved) {
            throw new PostingException('An unapproved expense cannot be posted.');
        }
        $claim->loadMissing('lines', 'company');
        $company = $claim->company;
        $payable = $claim->payable_account_code;
        $movements = [];
        $taxByAccount = [];
        foreach ($claim->lines as $line) {
            $dims = is_array($line->dimensions) ? $line->dimensions : [];
            $movements[] = ['account' => $line->expense_account_code, 'debit' => Decimal::of($line->amount), 'memo' => $line->description ?? 'Expense', 'dimensions' => $dims];
            if (Decimal::isPositive(Decimal::of($line->tax_amount)) && is_string($line->tax_account_code)) {
                $taxByAccount[$line->tax_account_code] = Decimal::add($taxByAccount[$line->tax_account_code] ?? '0', Decimal::of($line->tax_amount));
            }
        }
        foreach ($taxByAccount as $code => $amt) {
            $movements[] = ['account' => $code, 'debit' => $amt, 'memo' => 'Input tax'];
        }
        $movements[] = ['account' => $payable, 'credit' => Decimal::of($claim->total_amount), 'memo' => 'Employee payable'];

        $journal = $this->poster->post($company, 'expense.claim', $claim->claim_date->toDateString(), (string) ($claim->currency ?? $company->functional_currency), $claim->number, $movements);
        $claim->forceFill(['status' => 'posted', 'journal_id' => $journal->id])->save();
        $this->audit->record($claim, 'posted', $company->id, null, ['journal_id' => $journal->id]);

        return $claim->fresh(['lines']) ?? $claim;
    }
}
