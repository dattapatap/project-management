# WMS ERP — Agent Instructions

> **Read ALL linked documents before writing any code.**

## Project Summary
This is a Laravel 12 ERP application (Digitalnock IT Solutions) with 3 departments: Sales (NSD), Operations (OD), and Customer Service (CSD). It uses Blade + jQuery + Bootstrap 4, with Spatie Permission for RBAC.

## Mandatory Reading Order
Before making ANY code changes, read these documents in order:

1. **[MEMORY.md](file:///d:/Web%20Applications/WMS/.agents/MEMORY.md)** — Gotchas, known pitfalls, key decisions (read FIRST)
2. **[RULES.md](file:///d:/Web%20Applications/WMS/.agents/RULES.md)** — Coding standards and rules (what you MUST follow)
3. **[ARCHITECTURE.md](file:///d:/Web%20Applications/WMS/.agents/ARCHITECTURE.md)** — System architecture and patterns
4. **[PRD.md](file:///d:/Web%20Applications/WMS/.agents/PRD.md)** — Product requirements and business context
5. **[DESIGN.md](file:///d:/Web%20Applications/WMS/.agents/DESIGN.md)** — Design system, CSS classes, component library
6. **[PHASES.md](file:///d:/Web%20Applications/WMS/.agents/PHASES.md)** — Project phases and refactoring roadmap

## Top 5 Rules (Non-Negotiable)

1. **NO inline CSS** — Use classes from `erp-theme.css` / `erp-components.css` or create new ones
2. **NO business logic in controllers** — Use Services (`app/Services/`)
3. **NO raw SQL with string concatenation** — Use Eloquent or parameterized queries
4. **NO `$guarded = []`** — Always use `$fillable` on models
5. **NO `env()` in views** — Use `config()` helper

## Quick Reference

| Dept ID | Department | Color | Module Class |
|---------|-----------|-------|-------------|
| 1 | NSD (Sales) | Purple `#7F00FF` | `nsd-module` |
| 2 | OD (Operations) | Blue `#2E86DE` | `od-module` |
| 3 | CSD (Customer Service) | Teal `#10AC84` | `csd-module` |

## Where to Put Things

| What | Where |
|------|-------|
| Business logic | `app/Services/{Module}/` |
| Data access queries | `app/Repositories/` |
| Form validation | `app/Http/Requests/` |
| New CSS classes | `public/css/erp-components.css` or module-specific CSS |
| Reusable UI | `resources/views/components/` or Blade components |
| Global helpers | `app/Helper/helper.php` (but prefer services) |

## After Every Change

> AI agents MUST update [MEMORY.md](file:///d:/Web%20Applications/WMS/.agents/MEMORY.md) sections:
> - **Section 2** (Currently Working On) — What files you're modifying
> - **Section 3** (Model Status) — If you touch any model
> - **Section 7** (Change Log) — One-line summary of what changed
