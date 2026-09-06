# Phase D — General Ledger & Period Close (Implemented & Gated)

GL close layer on the existing Journal/Period foundation: **Manual/Recurring Journals → Accruals/Prepayments (Engine) → Month-end Checklist → Soft/Hard/Lock → Year-end RE**. Status: **implemented & gated**.

## D.0 Support schema
- `recurring_journal_templates` + lines
- `accruals` (auto-reverse date = next period start)
- `prepayments` + `prepayment_schedules`
- `period_close_runs` + checklist items
- `PeriodStatus::LOCKED` (terminal; no routine reopen)

## D.1 Manual & recurring journals
| Service | Behaviour |
|---|---|
| `ManualJournalService` | Draft → submit → approve → post (or void). Posts only through `JournalService`. |
| `RecurringJournalService` | Balanced templates; `runDue` posts system journals and advances `next_run_date`. |

## D.2 Accruals & prepayments
| Type | Rule | Journal |
|---|---|---|
| Accrual | `gl.accrual` | Dr Expense · Cr Accrued liability (`210203` / chart) |
| Accrual reverse | `JournalService::reverse` | Auto on `reversal_date` |
| Prepayment | `gl.prepayment` | Dr Prepaid · Cr funding |
| Amortization | `gl.prepayment_amortization` | Dr Expense · Cr Prepaid |

## D.3 Period close
`PeriodCloseService::run` checklist:

| Code | Check |
|---|---|
| AR_RECON / AP_RECON | INV-7 / INV-8 |
| BANK_RECON | INV-6 (or pass if no bank accounts) |
| INTEGRITY / TB / FS | INV-1..3 + equation |
| SUSPENSE | INV-11 |
| ACCRUALS / PREPAYMENTS | No drafts / overdue amortizations |
| INV_SKIP / FA_SKIP | INV-9 / INV-10 skipped until Phases H/G |

`closeIfPassed` → `PeriodService::softClose` / `hardClose` / `lock`. Reopen requires reason; locked periods cannot reopen.

## D.4 Year-end
`YearEndCloseService` zeros temporary P&L accounts into retained earnings (`320201` / `closing_behavior=retained_earnings`).

## Deferred
Attachments, full SoD/RBAC on approve/post (Phase P), inventory/FA close checks (G/H), FX reval (E).
