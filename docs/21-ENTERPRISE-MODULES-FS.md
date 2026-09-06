# Phases F–S — Implemented & Gated (domain)

Domain services only (no HTTP/UI). Every journal still goes **Source → PayloadMovementsRule/existing rules → AccountingEngine → GL**.

| Phase | Module | Entry |
|---|---|---|
| F | Tax | `TaxEngine` (configured rates + legal_reference) · `TaxService` VAT settle / WHT / deferred / adjustment |
| G | Fixed assets | `AssetService` acquire / SL depreciate / impair / transfer / dispose · INV-10 |
| H | Inventory | `InventoryService` receipt (GRNI `210203`) / issue COGS / warehouse transfer (no GL) / adjust · INV-9 |
| I | IFRS 15 | `RevenueRecognitionService` cash → unearned `210301` → recognize `410301` |
| J | IFRS 16 | `LeaseService` ROU `120112` / liability `220301` / interest `630106` / dep `620407` |
| K | Loans | `LoanService` drawdown / repay (principal + `630101`) |
| L | Payroll | `PayrollService` accrue (caller-supplied statutory amounts) / pay net |
| M | Budget | `BudgetService` set / variance vs actual |
| N | Cost | `CostAllocationService` |
| O | Consolidation | `ConsolidationService` IC elimination |
| P | Access / SoD | `AccessControl` · SoD-1 on manual approve/post · `period.reopen` |
| Q | Risk | `RiskService` duplicate invoices / backdated postings |
| R | Cash flow | `CashFlowService` 1101* inflows/outflows |
| S | Assistant | `AccountingAssistantService` draft → review → approve → post via `ManualJournalService` |

Tax rates are **never assumed**. Period close now runs INV-9 and INV-10 (empty subledgers pass 0=0).
