# Phase AD — Production Frontend

Arabic-first Inertia/Vue ERP UI consuming `/api/v1`.

## Stack

- Vue 3 + Inertia + Pinia + vue-i18n
- Tailwind 4 + reka-ui (existing)
- IBM Plex Sans Arabic + Instrument Sans
- Default locale: `ar` / `dir=rtl` (persisted in `erp.locale`)

## Context

Header `ContextBar`: company · book (LOCAL/IFRS/TAX) · period · language.

API: `GET /api/v1/me/context` (+ Sanctum stateful session for same-origin UI).

Storage: `erp.companyId`, `erp.bookId`.

## Modules

Dashboard, AR, AP, GL, Expenses, Projects, Investments, Tax, Opening balances, Books, Reports.

Banking: honest empty state until treasury HTTP routes exist (no mock data).

## Rules

- No accounting math in Vue — display API amounts only
- No hard-coded tax rates
- Permission-aware sidebar via AccessGrant permissions

## Tests

```bash
npm run test
npm run types:check
npm run build
```

Demo user: `test@example.com` / `password` (after `DemoUserSeeder`).
