# Phase B — Accounts Payable (Implemented & Gated)

AP subledger built to the same enterprise standard as AR: every journal goes through the **Accounting Engine** (no direct GL), reconciled to the AP control account resolved from the chart. Status: **implemented & gated** — Pint clean, PHPStan L7 = 0, 115 Pest tests green (incl. AP scenario matrix), INV-8 AP↔GL, TB & FS balanced.

## Canonical AP control
The control account is **not hard-coded**. `ControlAccountResolver` selects the posting control account flagged `is_control` + `subledger_mapping = AP` (and/or `is_supplier_subledger`). On the 330 enterprise chart that is **210101 — Trade Payables (الموردون)**. `220201` in this chart is End-of-Service Benefits, not AP.

Each supplier stores the resolved `ap_control_code` at create time.

## Data model (migrations `000013–000015`)
- **Master:** `suppliers` (number, legal/trading name, tax ID, currency, payment terms, credit limit, default expense/inventory accounts, dimensions, active/blocked), `supplier_contacts`, `supplier_addresses`, `supplier_bank_accounts`, `supplier_tax_profiles`. Payment terms reused from AR.
- **Documents:** `purchase_invoices`(+lines), `purchase_credit_notes`(+lines), `purchase_debit_notes`(+lines) — each links immutable `journal_id` on posting. Unique supplier invoice number per supplier. Tax is caller-supplied per line (Phase F).
- **Cash application:** `supplier_payments`, `ap_allocations` (payment XOR credit note).

## Accounting rules (via `AccountingEngine`)
| Document | Journal |
|---|---|
| Purchase invoice | `Dr Expense/Inventory · Dr Input VAT · Cr AP control` |
| Supplier payment | `Dr AP · Cr Bank/Cash` |
| Credit note | `Dr AP · Cr Expense/Inventory · Cr Input VAT` |
| Debit note | `Dr Expense/Inventory · Dr Input VAT · Cr AP` |

## Services (`App\Services\Ap`)
`SupplierService` · `PurchaseInvoiceService` (post + reverse) · `SupplierPaymentService` (post, allocate, allocateMany, reverse) · `PurchaseCreditNoteService` (auto-allocates to original invoice) · `PurchaseDebitNoteService` · `ApLedgerService` (open items · aging 0/1–30/31–60/61–90/91–120/120+ · supplier statement) · `ApReconciliationService` (INV-8).

## Deferred
Tax computation (Phase F), FX gain/loss on cross-currency settlement (Phase E), RBAC (Phase P).
