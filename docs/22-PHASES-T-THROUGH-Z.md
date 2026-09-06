# Phases T–Z — Production Implementation (Gated)

Domain services only (**no HTTP/UI**). All journals: **Source → Rule → AccountingEngine → GL**.

## Architecture

```
Domain Service → AccountRoleResolver / TaxResolver → EnginePoster → AccountingEngine → Journal → Book (LOCAL|IFRS|TAX) → ReportingPack
```

Semantic CoA roles live in `AccountRoleResolver` (optional per-company `account_roles` overrides). Legal rates live only in versioned localization / tax configuration.

## T — Investments (IFRS 9)

| Piece | Detail |
|---|---|
| Models | `Investment`, `InvestmentTransaction`, `InvestmentValuation`, `InvestmentIncome`, `InvestmentDisposal` |
| Services | `InvestmentService`, `InvestmentIncomeService` |
| Classifications | `FVTPL` · `FVOCI` · `AMORTIZED_COST` |
| Entries | Acquire Dr Investment / Cr Bank · FVTPL P&L · FVOCI → `investment.oci_reserve` · EIR interest increases carrying · Dividend/interest income · Disposal with gain/loss (+ OCI recycle for FVOCI) |

## U — Expenses

Workflow: **Draft → Submitted → Approved|Rejected → Posted → Reimbursed**  
Rejected claims never post. Unapproved claims cannot post unless `allowUnapproved`.  
Posting: Dr Expense (+ input tax) / Cr `expense.employee_payable` (role).  
`ExpenseReimbursementService` supports partial/full reimbursement and reversal via journal reverse.

## V — Projects

`ProjectCostService::charge` (traceable source) · `ProjectCapitalizationService` → CWIP role `project.cwip` · `ProjectReportingService` (charged / capitalized / budget / variance).

## W — Libya localization

Versioned `LocalizationRule` · `LegalInvoiceNumberService` (company + document + period sequences, audit) · `TaxResolver` (no hard-coded rates) · `CountryPackService::vatReturn` from posted GL via account roles + `legal_reference`.

## X — Opening balances

`OpeningBalanceBatch` / `OpeningBalanceLine`: Draft → Validated → Posted → Locked.  
Only permanent accounts (`opening_balance_allowed`). Posts through `opening.balance` rule. Duplicate batch per company/book/as_of rejected.

## Y — Multi-book

`MultiBookService::postParallel` applies `BookAccountMapping` + `BookPostingRule` per book. Closed books (`is_active=false`) reject. Trial balance / reports always scoped by `book_id`.

## Z — Reporting pack

`ReportingPackService` / `CashFlowService` (operating/investing/financing with journal line traceability) · OCI with journal ids · AR/AP forecast · IFRS notes · investment/tax/project/budget pack · explicit `book_basis`.

## Invariants (suite)

`tests/Feature/Enterprise/GlobalInvariantSuiteTest.php` covers INV-1…INV-12 (balanced journals, accounting equation, document→journal, period lock, AR/AP recon, no orphans, opening-batch balance, book consistency).

## Failure cases covered

Invalid classification / zero qty-amount · expense reject-before-post · duplicate claim · over-capitalization · P&L opening · unbalanced opening · closed book · closed period · missing localization rule.
