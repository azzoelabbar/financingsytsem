<?php

declare(strict_types=1);

namespace App\Services\Expense;

use App\Models\Expense\ExpenseClaim;
use App\Models\Expense\ExpenseReimbursement;
use App\Services\Accounting\AccountRoleResolver;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Carbon;

class ExpenseReimbursementService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly AccountRoleResolver $roles,
        private readonly JournalService $journals,
        private readonly AuditLogger $audit,
    ) {}

    public function reimburse(ExpenseClaim $claim, string $date, string|float|int|null $amount = null): ExpenseReimbursement
    {
        if ($claim->status !== 'posted' && $claim->status !== 'reimbursed') {
            throw new PostingException('Only a posted claim can be reimbursed.');
        }
        $open = Decimal::sub(Decimal::of($claim->total_amount), Decimal::of($claim->reimbursed_amount));
        $pay = $amount === null ? $open : Decimal::of($amount);
        if (! Decimal::isPositive($pay)) {
            throw new PostingException('Reimbursement amount must be positive.');
        }
        if (Decimal::compare($pay, $open) > 0) {
            throw new PostingException('Reimbursement exceeds the open claim balance.');
        }
        $company = $claim->company;
        $journal = $this->poster->post($company, 'expense.reimburse', $date, (string) ($claim->currency ?? $company->functional_currency), $claim->number, [
            ['account' => $claim->payable_account_code, 'debit' => $pay, 'memo' => 'Reimburse claim'],
            ['account' => $this->roles->code($company, 'bank'), 'credit' => $pay, 'memo' => 'Bank'],
        ]);
        $reimbursed = Decimal::add(Decimal::of($claim->reimbursed_amount), $pay);
        $status = Decimal::equals($reimbursed, Decimal::of($claim->total_amount)) ? 'reimbursed' : 'posted';
        $claim->forceFill(['reimbursed_amount' => $reimbursed, 'status' => $status])->save();
        $row = $claim->reimbursements()->create([
            'paid_on' => $date,
            'amount' => $pay,
            'journal_id' => $journal->id,
            'status' => 'posted',
        ]);
        $this->audit->record($claim, 'allocated', $company->id, null, ['reimbursement_id' => $row->id, 'journal_id' => $journal->id]);

        return $row;
    }

    public function reverse(ExpenseReimbursement $reimbursement, string $date, ?string $reason = null): ExpenseReimbursement
    {
        if ($reimbursement->status !== 'posted') {
            throw new PostingException('Reimbursement is not posted.');
        }
        $this->journals->reverse($reimbursement->journal, date: Carbon::parse($date), reason: $reason);
        $claim = $reimbursement->claim;
        $open = Decimal::sub(Decimal::of($claim->reimbursed_amount), Decimal::of($reimbursement->amount));
        $claim->forceFill([
            'reimbursed_amount' => $open,
            'status' => 'posted',
        ])->save();
        $reimbursement->forceFill(['status' => 'reversed'])->save();
        $this->audit->record($claim, 'reversed', $claim->company_id, null, ['reimbursement_id' => $reimbursement->id]);

        return $reimbursement;
    }
}
