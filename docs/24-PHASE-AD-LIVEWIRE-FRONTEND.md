# Phase AD — Blade + Livewire Frontend

## Architecture

- **Presentation:** Laravel Blade
- **Reactive UI:** Livewire 4
- **Styling:** Tailwind CSS 4
- **Client JS:** Alpine (via Livewire) only for lightweight UI state
- **Removed:** Inertia.js, Vue, Pinia, Vue Router as the application frontend

Legacy Vue/Inertia sources (if present) live under `resources/js-inertia-legacy/` and are not part of the Vite build.

## Defaults

| Context | Default |
|---------|---------|
| Locale | `ar` |
| Direction | RTL |
| Book | LOCAL |
| Company | Auto-selected when only one grant exists |

Language preference persists via session + forever cookie (`locale`).

## Shell

- Expandable sidebar (AR / AP / GL / Reports / Operations)
- Header: company, book, period, language, user
- Design tokens: ink navy, warm paper, teal accent (`resources/css/app.css`)
- UI primitives: `x-ui.*` under `resources/views/components/ui/`

## Livewire map

`app/Livewire/{Dashboard,Ar,Ap,Gl,Banking,Expenses,Projects,Investments,Tax,OpeningBalances,Books,Reports,Layout}`

Business logic stays in Application / Domain services (`ArApplicationService`, `GlApplicationService`, etc.). Livewire does not calculate VAT, journals, statements, or FX.

## Quality

- Feature / Livewire tests under `tests/Feature/Livewire` and `tests/Feature/DashboardTest.php`
- Production build: `npm run build`
- Backend gate unchanged: Pint, PHPStan L7, Pest
