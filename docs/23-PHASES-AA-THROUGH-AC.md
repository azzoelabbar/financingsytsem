# Phases AA–AC — HTTP/API Application Layer

## Architecture

```
HTTP Request
  → Form Request / Validation
  → Authorization / AccessControl (company-scoped grants)
  → Application Service (App\Application\Api\*)
  → Domain Service
  → AccountingEngine
  → Journal / Subledger / Books
  → Reporting
```

Controllers contain **no** accounting logic.

## AA — `/api/v1`

| Area | Prefix | Permissions |
|------|--------|-------------|
| Auth | `/auth/token` | public issue / sanctum revoke |
| AR | `/ar/*` | `ar.read` / `ar.write` |
| AP | `/ap/*` | `ap.read` / `ap.write` |
| GL | `/gl/*` | `gl.read` / `gl.write` |
| Investments | `/investments` | `investments.*` |
| Expenses | `/expenses` | `expenses.*` |
| Projects | `/projects` | `projects.*` |
| Tax | `/tax/codes` | `tax.*` |
| Opening balances | `/opening-balances` | `opening_balances.*` |
| Books | `/books` | `books.read` |
| Reports | `/reports/*` | `reports.read` |

**Headers:** `Authorization: Bearer …`, `X-Company-Id`, optional `X-Book-Id` / `X-Book-Code` (default LOCAL).

**Responses:** `{ success, data, meta.pagination }` · errors `{ success:false, error:{ code, message } }`.

## AB — Security tests

Covered in `tests/Feature/Api/ApiV1FoundationAndSecurityTest.php`:

| Case | Expected |
|------|----------|
| Unauthenticated | 401 |
| Missing role/permission | 403 |
| Wrong company (no grant) | 403 |
| Wrong book id | 404 |
| Cross-company document id | 404 |
| Posted document re-post | 422 `business_rule` |
| Hard-closed period post | 422 `business_rule` |

## AC — OpenAPI contract

Canonical contract: [`docs/api/openapi-v1.yaml`](api/openapi-v1.yaml)

Documents method, URL, auth, permissions, parameters, bodies, validation/business errors, and status codes for frontend/mobile clients.

## Pre-AA verification

Production-style E2E: `tests/Feature/Enterprise/ProductionEndToEndScenarioTest.php`  
(Company → AR FX → AP → Investments/Expenses/Projects/Tax/OB → multi-book → TB/BS/P&L/CF/pack + immutability + closed period).

## AD — Frontend

**Not started.** Build UI only after AA–AC remain stable; UI must call API endpoints (e.g. `/ar/aging`, `/reports/balance-sheet`) and must not recompute accounting in Vue/JS.
