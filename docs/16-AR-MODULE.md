# Phase A — Accounts Receivable (Implemented)

AR subledger built to enterprise standards, all journals via the **Accounting Engine** (no direct GL), reconciled to the AR control account. Status: **implemented & gated** — 80 tests green, PHPStan L7 = 0, Pint clean.

## Prerequisite (A.0) — canonical chart
The **330-account enterprise chart is now canonical** (`EnterpriseChartOfAccountsSeeder`, seeded by `DemoCompanySeeder`). The 168 chart is legacy/back-compat only (`ChartOfAccountsSeeder`, exercised by `ChartOfAccountsTest`). Additive migration `000008` added `subledger_mapping`, `is_intercompany`, `is_suspense`, `is_oci`, `eliminate_on_consolidation`; enums gained `AccountType::MEMO` and `ClosingBehavior::OCI/NONE`. Nothing in the 168 chart was modified.

## Data model (migrations `000009–000012`)
- **Master:** `customers`, `customer_groups`, `customer_contacts`, `customer_addresses`, `credit_profiles`, `payment_terms`.
- **Documents:** `sales_invoices`(+lines), `sales_credit_notes`(+lines), `sales_debit_notes`(+lines) — each links its immutable `journal_id` on posting; tax carried per line (caller-supplied until Tax Engine, Phase F).
- **Cash application:** `receipts`, `ar_allocations` (source = receipt XOR credit note, relational not polymorphic).
- **Collections/IFRS 9:** `collection_activities`, `ecl_assessments`, `bad_debt_writeoffs`.
- Customer ledger/aging are **derived** from documents + allocations (single source of truth), returned in functional currency.

## Accounting rules (via `AccountingEngine`)
| Document | Journal |
|---|---|
| Sales invoice | `Dr AR control · Cr Revenue (per line) · Cr Output VAT` |
| Customer receipt | `Dr Bank/Cash · Cr AR` |
| Credit note | `Dr Revenue · Dr Output VAT · Cr AR` |
| Debit note | `Dr AR · Cr Revenue · Cr Output VAT` |
| Bad debt | covered → `Dr Allowance · Cr AR`; uncovered → `Dr Bad-Debt Expense · Cr AR` |
| ECL (IFRS 9) | `Dr ECL Expense · Cr Loss Allowance` (methodology is input, not hard-coded) |

## Services (`App\Services\Ar`)
`CustomerService` · `SalesInvoiceService` · `ReceiptService` (post + allocate, same-currency) · `CreditNoteService` (auto-allocates to the original invoice) · `DebitNoteService` · `BadDebtWriteOffService` · `EclService` (allowance is an input; methodology never hard-coded) · `CollectionService` · `ArLedgerService` (open items · aging buckets · customer statement · subledger total) · `ArReconciliationService` (INV-7).

## Reconciliation & integrity
`ArReconciliationService::assert()` compares the AR subledger open total to the GL balance of the AR control (`110201`) and throws `IntegrityViolationException` on any difference (INV-7). Core invariants INV-1/INV-2 and financial statements remain balanced throughout.

## Definition of Done (met)
End-to-end scenario green — **Company → Customer → Invoice → Posted Journal → Partial Receipt → Allocation → Aging → Reconciliation → Credit Note** — with: Debit=Credit, Assets=Liabilities+Equity, AR subledger = AR control, TB & FS balanced, every invoice carries `journal_id`, posted documents immutable (corrected by credit note/reversal), full audit trail, and the full quality gate (Pint → PHPStan L7 → Pest → Integrity → AR/GL reconciliation).

## Deferred (by design, later phases)
Tax computation (Phase F — currently caller-supplied), FX gain/loss on cross-currency settlement (Phase E), recurring-invoice generation (§56 engine), RBAC/SoD enforcement (Phase P — services are enforcement-ready).
