# Phase E — Multi-Currency / FX (Implemented & Gated)

IAS 21 FX on the existing engine: **Rates → Convert → Revalue (unrealized) → Settle (realized) → Translate (OCI)**. Status: **implemented & gated**.

## E.0 Rates
`ExchangeRateService`: store/resolve `(from, to, type, date)` with prior-date fallback and inverse-pair support. Types: spot / closing / average / historical (`RateType`).

## E.1 Transaction currency
Foreign-currency journals already convert line amounts × rate into functional on post (`JournalService`). AR/AP documents carry `currency` + `exchange_rate`; ledgers use `effectiveFxRate()` (`revaluation_rate ?? exchange_rate`).

## E.2 Unrealized revaluation
`FxRevaluationService::revalueOpenItems` at closing rate for open FC AR/AP:
- Updates document `revaluation_rate`
- Posts via `gl.fx_revaluation` → gain `420104` / loss `630105` vs AR/AP control

## E.3 Realized on settlement
Same-currency allocation at a different rate (AR receipt / AP payment):
- Allowed (rate lock removed)
- `FxSettlementService` posts `gl.fx_realized` → `420104` / `630104`
- Still rejects **different currencies** (cash must match invoice currency)

## E.4 Translation (OCI)
`FxTranslationService` when `presentation_currency ≠ functional_currency`: residual to CTA `330102` (contra `320202`) via `gl.fx_translation`.

## CoA used
| Code | Role |
|---|---|
| 420104 | Realized (and unrealized) FX gains |
| 630104 | Realized FX losses |
| 630105 | Unrealized FX losses |
| 330102 | Translation reserve (OCI) |

## Deferred
Full consolidation translation worksheets, bank-feed rate imports, true cross-currency cash application (USD invoice / LYD receipt).
