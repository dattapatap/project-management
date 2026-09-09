# 🚀 Phases — Project Breakdown

## Project: WMS — Workforce Management System / ERP

> The project is broken into 6 phases, ordered by priority.
> Each phase can be independently executed.
> **Phase 1 is mandatory** before any other work.

---

## Phase 1: 🔴 Security & Foundation Fixes

> **Priority:** CRITICAL — Must be done first  
> **Estimated Effort:** 2-3 days  
> **Goal:** Eliminate security vulnerabilities and establish baseline safety

### 1.1 Remove Public SQL Dumps
- [ ] Delete `public/db_erp_digitalnock.sql` (~12MB)
- [ ] Delete `public/db_erp_digitalnock_final.sql` (~13MB)
- [ ] Add `*.sql` to `.gitignore`

### 1.2 Fix SQL Injection Vulnerabilities
- [ ] `DashboardController::chartdata()` — Replace 4x raw `DB::select()` with parameterized queries
- [ ] Audit `ReportController` for raw SQL with string concatenation
- [ ] Audit `helper.php` global functions for raw queries
- [ ] Convert all remaining `DB::select()` calls to Eloquent or parameterized `DB::select('...?', [$param])`

### 1.3 Fix `env()` Usage in Code
- [ ] `home.blade.php` line 30: `env('APP_NAME')` → `config('app.name')`
- [ ] `ReportController.php` line 82: `env('APP_URL')` → `url('/')`
- [ ] Search and replace all `env(` in views and controllers

### 1.4 Fix Model Mass Assignment
- [ ] Models with `$guarded = []` that need `$fillable`:
  - [ ] `Clients.php`
  - [ ] `ClientHistory.php`
  - [ ] `ClientEngagement.php`
  - [ ] `ClientEngagementEvent.php`
  - [ ] `Task.php`
  - [ ] `DepartmentProjects.php`
  - [ ] `DepartmentProjectHistory.php`
  - [ ] `CsdClientAssignment.php`
  - [ ] `CsdAmcContract.php`
  - [ ] `CsdRenewal.php`
  - [ ] `CsdSupportTicket.php`
  - [ ] `CsdChangeRequest.php`
  - [ ] `CsdCommunication.php`
  - [ ] `CsdCollectionFollowup.php`
  - [ ] `CsdContactPerson.php`
  - [ ] `CsdOpportunity.php`

---

## Phase 2: 🔴 Inline CSS Cleanup & Design Consistency

> **Priority:** CRITICAL — Design is currently broken by scattered inline styles  
> **Estimated Effort:** 3-5 days  
> **Goal:** Move ALL inline CSS to proper CSS files, enforce design system

### 2.1 Create Missing CSS Utility Classes
- [ ] Add to `erp-theme.css`:
  - `.icon-lg` (font-size: 28px)
  - `.gap-2`, `.gap-3`, `.gap-4` (gap: 8px / 12px / 16px)
  - `.scroll-sm`, `.scroll-md` (max-height overflow scrolls)
  - `.tracking-wide` (letter-spacing: 0.5px)
- [ ] Add to `erp-components.css`:
  - `.kpi-card--success`, `.kpi-card--warning`, `.kpi-card--danger`, `.kpi-card--info`, `.kpi-card--purple`
  - `.btn-gradient-purple` (Sales CTA button)
  - `.year-filter-select` (Year selector styling)
  - `.sales-welcome-card` (Welcome banner)

### 2.2 Clean Dashboard Views — Admin
- [ ] `admin_ui.blade.php` — Extract all KPI card gradient backgrounds to CSS classes
- [ ] Remove inline `font-size`, `border-radius`, `background: linear-gradient(...)` from cards

### 2.3 Clean Dashboard Views — Sales
- [ ] `sales_ui.blade.php` (944 lines) — Extract all inline styles (gradients, border-radius, padding, gaps)
- [ ] `tl_sales_ui.blade.php` — Same treatment
- [ ] `sales_scripts.blade.php` — Remove any inline styles injected via JS

### 2.4 Clean Dashboard Views — OD & Others
- [ ] `employee_od_ui.blade.php` — Extract timeline and card inline styles
- [ ] `tl_od_ui.blade.php` — Extract table and oversight card styles
- [ ] `pm_ui.blade.php` — Extract project card styles
- [ ] `branch_manager_ui.blade.php` — Extract oversight card styles

### 2.5 Clean Dashboard Views — CSD
- [ ] `csd_ui.blade.php` — Extract CSD-specific styles
- [ ] `tl_csd_ui.blade.php` — Extract TL-specific CSD styles

### 2.6 Clean Component Views
- [ ] Client views (`components/clients/`) — Remove inline styles
- [ ] Project views (`components/projects/`) — Remove inline styles
- [ ] Report views (`components/reports/`) — Remove inline styles
- [ ] Day-closing views — Remove inline styles
- [ ] Target views — Remove inline styles

### 2.7 Clean Layout File
- [ ] `app.blade.php` — Remove inline styles from header/sidebar elements
- [ ] `home.blade.php` — Remove day-closing banner inline styles
- [ ] Move `@php` business logic blocks out of layout

---

## Phase 3: 🟡 Controller & Service Layer Refactoring

> **Priority:** HIGH — Improve code maintainability and testability  
> **Estimated Effort:** 5-7 days  
> **Goal:** Make controllers thin, move all logic to services

### 3.1 Break Up HomeController (614 lines → ~50 lines)
- [ ] Create `App\Services\Admin\AdminDashboardService` ← extract `getAdminDashboardData()`
- [ ] Create `App\Services\Od\PMDashboardService` ← extract `getPMDashboardData()`
- [ ] Create `App\Services\Od\OdTeamDashboardService` ← extract `getWmsTLDashboardData()`
- [ ] Create `App\Services\Od\OdEmployeeDashboardService` ← extract `getWmsEmployeeDashboardData()`
- [ ] HomeController becomes a simple router: check role → call service → return view

### 3.2 Break Up ClientsController (957 lines → ~150 lines)
- [ ] Move DataTable action column HTML to `views/components/clients/partials/action-buttons.blade.php`
- [ ] Create `App\Services\Sales\ClientAssignmentService` ← assignment logic
- [ ] Create `App\Services\Sales\ClientBulkUploadService` ← bulk upload logic
- [ ] Create `App\Services\Sales\ClientNudgeService` ← nudge/notification logic
- [ ] Move direct-mature logic to existing `ClientServices`

### 3.3 Break Up ReportController (574 lines → ~80 lines)
- [ ] Create `App\Services\Reports\StsReportService` ← STS search/filter logic
- [ ] Create `App\Services\Reports\DsrReportService` ← DSR search/filter logic
- [ ] Create `App\Services\Reports\SalesReportService` ← sales report aggregation

### 3.4 Fix DashboardController (Raw SQL)
- [ ] Create `App\Services\ChartDataService` ← consolidate 4 duplicate SQL queries
- [ ] Replace raw SQL string concatenation with Eloquent scopes
- [ ] DashboardController becomes a thin wrapper

### 3.5 Remove HTML from All Controllers
- [ ] Create `views/components/clients/partials/action-buttons.blade.php`
- [ ] Update all DataTable `addColumn('action', ...)` to use Blade partials
- [ ] Apply same pattern to ReportController DataTable renderers

---

## Phase 4: 🟡 Model & Data Layer Improvements

> **Priority:** HIGH  
> **Estimated Effort:** 2-3 days  
> **Goal:** Standardize models, create constants, expand repository coverage

### 4.1 Create Department Enum
- [ ] Create `App\Enums\DepartmentType` (or `App\Constants\Department`)
  ```php
  class Department {
      const NSD = 1;  // New Sales Department
      const OD  = 2;  // Operations Department
      const CSD = 3;  // Customer Service Department
  }
  ```
- [ ] Replace all hardcoded `== 1`, `== 2`, `== 3` across controllers, services, and views

### 4.2 Standardize Model Conventions
- [ ] Add `$casts` for date fields and booleans on all models
- [ ] Add return types to all relationship methods
- [ ] Consider renaming plural models to singular (e.g., `Clients` → `Client`) — plan carefully

### 4.3 Expand Repository Layer
- [ ] Create `UserRepository` for complex user/team member queries
- [ ] Create `DashboardRepository` for dashboard aggregation queries
- [ ] Create `ReportRepository` for report-specific complex queries

### 4.4 Add Missing FormRequest Classes
- [ ] Create FormRequest for: DayClosing, DailyTarget, Department, Team, Project, CSD operations
- [ ] Remove all inline `Validator::make()` calls from controllers

---

## Phase 5: 🟡 View Decomposition & Component System

> **Priority:** MEDIUM  
> **Estimated Effort:** 3-4 days  
> **Goal:** Break down large view files, create reusable Blade components

### 5.1 Break Down Large Dashboard Views
- [ ] `sales_ui.blade.php` (944 lines) → Split into partials:
  - `dashboards/sales/welcome-header.blade.php`
  - `dashboards/sales/kpi-cards.blade.php`
  - `dashboards/sales/team-oversight.blade.php`
  - `dashboards/sales/pipeline-chart.blade.php`
  - `dashboards/sales/callback-list.blade.php`
  - `dashboards/sales/leaderboard.blade.php`
- [ ] Same decomposition for `employee_od_ui.blade.php`, `tl_od_ui.blade.php`

### 5.2 Break Down Main Layout
- [ ] `app.blade.php` (806 lines) → Extract:
  - `layouts/partials/header.blade.php`
  - `layouts/partials/sidebar.blade.php`
  - `layouts/partials/footer.blade.php`
  - `layouts/partials/global-scripts.blade.php`
- [ ] Move `@php` blocks from layout to View Composer or middleware

### 5.3 Create Reusable Blade Components
- [ ] `<x-kpi-card :value :label :gradient :href />`
- [ ] `<x-metric-badge :count :variant :label />`
- [ ] `<x-department-color :dept />` (auto-applies correct accent)
- [ ] `<x-erp-page-header :title :subtitle />` (page header with breadcrumbs)

### 5.4 Consolidate JavaScript
- [ ] Extract common patterns from `*_scripts.blade.php` to `public/js/erp-common.js`
- [ ] Remove duplicate chart initialization code
- [ ] Standardize AJAX error handling across all scripts

---

## Phase 6: 🟢 Testing & Code Health

> **Priority:** NICE TO HAVE  
> **Estimated Effort:** 3-5 days  
> **Goal:** Add automated tests, remove dead code, tooling

### 6.1 Add Feature Tests
- [ ] Dashboard route access tests per role
- [ ] Client CRUD flow tests
- [ ] Report generation tests
- [ ] Day-closing submission + approval tests

### 6.2 Add Unit Tests
- [ ] Service layer tests (mock repositories)
- [ ] Helper function tests
- [ ] FormRequest validation tests

### 6.3 Code Health
- [ ] Configure Laravel Pint for auto-formatting
- [ ] Add PHPStan (level 5+) for static analysis
- [ ] Remove all commented-out code
- [ ] Remove unused imports and dead methods
- [ ] Remove orphaned view files

### 6.4 Documentation
- [ ] Add PHPDoc to all public service methods
- [ ] Add `@method` annotations to models
- [ ] Document all API endpoints (if expanding API)

---

## Execution Order

```
Phase 1 (Security) ──MUST DO FIRST──▶ Phase 2 (CSS) ──▶ Phase 3 (Controllers)
                                                               │
                                                     Phase 4 (Models) ──▶ Phase 5 (Views) ──▶ Phase 6 (Tests)
```

### Rules During Refactoring
1. **One phase at a time** — do not mix phases
2. **Test after each step** — verify the app still works
3. **Preserve behavior** — refactoring must not change what the user sees
4. **Separate commits** — each step = one clear git commit
5. **No new inline CSS** — enforce from Phase 2 onward
