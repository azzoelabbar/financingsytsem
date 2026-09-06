# Phase C — Cash & Banking (Implemented & Gated)

Treasury built on the same pipeline as AR/AP: **Business Document → AccountingEngine → Journal → Book balance → Reconciliation (INV-6) → Audit → Tests**. Status: **implemented & gated** — Pint clean, PHPStan L7 = 0, 129 Pest tests green, INV-6 bank/cash book = GL.

## C.0 Master
- `banks`, `treasury_accounts` (cash | bank) with `gl_account_code` validated against the chart (`is_bank_account` for bank accounts; cash resolves via `ControlAccountResolver::defaultCashCode()` → **110101**).
- Opening balance stored on the treasury account (GL opening journals remain Phase X).

## C.1 Cash transactions
Types: cash/bank receipt & payment, transfer, misc receipt/payment, bank fee (`630102`), bank interest (`420101`).

| Type | Journal |
|---|---|
| Receipt | `Dr Bank/Cash · Cr contra` |
| Payment / fee | `Dr contra · Cr Bank/Cash` |
| Transfer | `Dr destination · Cr source` |

All via `CashTransactionService` → `TreasuryReceiptRule` / `TreasuryPaymentRule` / `TreasuryTransferRule`.

## C.2 Bank reconciliation
`BankReconciliationService`: import statement → start session → auto-match (exact / reference / amount + date tolerance) or manual → refresh outstanding deposits/cheques → complete only when:

`statement + outstanding deposits − outstanding cheques = book balance` (INV-6)

`assert()` also compares book balance to the GL balance of the linked chart account.

## C.3 Cash controls
`CashControlService`: cash count vs system book; over/short posts as misc receipt/payment through the engine.

## Deferred
Statement file parsers (CSV/MT940) beyond array import, multi-currency settlement FX (Phase E), RBAC (Phase P).
