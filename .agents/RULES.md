# 📐 Rules — What to Use, What to Avoid

## Project: WMS — Workforce Management System / ERP

> These rules MUST be followed by every AI agent and developer.
> Rules tagged 🔴 are **blocking** — code violating them must not be merged.
> Rules tagged 🟡 are **important** — fix when touching the file.

---

## 1. What to Use ✅

### 1.1 Backend (PHP / Laravel)

| Category | Use This | Example |
|----------|----------|---------|
| **Business Logic** | Service classes in `app/Services/{Module}/` | `CsdRenewalService`, `SalesDashboardService` |
| **Data Access** | Repository classes in `app/Repositories/` | `ClientRepository`, `BaseRepository` |
| **Validation** | FormRequest classes in `app/Http/Requests/` | `ClientStoreRequest`, `TaskRequest` |
| **Authorization** | Spatie Permission (`hasRole()`, `can()`) + Middleware | `$user->hasRole('Admin')` |
| **Queries** | Eloquent ORM or parameterized `DB::` queries | `Client::where('status', 'Matured')->get()` |
| **Model Protection** | `$fillable` arrays on every model | `protected $fillable = ['name', 'email']` |
| **Config Values** | `config()` helper in all code | `config('app.name')` |
| **URLs** | `route()` and `url()` helpers | `route('clients.index')`, `url('/')` |
| **Error Handling** | Try-catch with logging in services | `try { ... } catch (\Exception $e) { Log::error(...) }` |
| **Date Handling** | Carbon (already in use everywhere) | `Carbon::today()`, `Carbon::parse($date)` |
| **Code Formatting** | Laravel Pint | `./vendor/bin/pint` |

### 1.2 Frontend (Blade / CSS / JS)

| Category | Use This | Example |
|----------|----------|---------|
| **Styles** | CSS classes from `erp-theme.css` / `erp-components.css` | `class="card sales-card-glass"` |
| **Grid** | Bootstrap 4 grid classes | `col-xl-3 col-md-6` |
| **Icons** | Boxicons (`bx bx-*`) or MDI (`mdi mdi-*`) | `<i class="bx bx-check"></i>` |
| **Dropdowns** | Select2 | `$('.select2').select2()` |
| **Tables** | Yajra DataTables (server-side) | `DataTables::of($query)` |
| **Alerts** | AlertifyJS | `alertify.success('Done')` |
| **Charts** | ApexCharts | `new ApexCharts(el, options)` |
| **AJAX** | jQuery `$.ajax()` or `$.get()` / `$.post()` | `$.post(url, data, callback)` |
| **Events** | jQuery event delegation | `$(document).on('click', '.btn', handler)` |
| **Templates** | Blade `@include` and `@component` | `@include('dashboards.admin_ui')` |

---

## 2. What to Avoid ❌

### 2.1 Backend Prohibitions

| 🔴 Rule | What NOT to Do | Do This Instead |
|---------|----------------|-----------------|
| **No inline CSS** | `style="background: red; padding: 10px;"` | Add a CSS class to `erp-components.css` |
| **No logic in controllers** | Queries, loops, calculations in controller methods | Move to Service class |
| **No raw SQL concat** | `DB::select('...WHERE id=' . $id)` | `DB::select('...WHERE id=?', [$id])` or Eloquent |
| **No `$guarded = []`** | `protected $guarded = [];` on models | Use `protected $fillable = [...]` |
| **No `env()` in views** | `{{ env('APP_URL') }}` in Blade | `{{ config('app.url') }}` or `{{ url('/') }}` |
| **No HTML in PHP** | Building `<a>`, `<button>` strings in controllers | Use Blade partials for DataTable columns |
| **No logic in Blade** | `@php $service = new Service(); @endphp` | Pass all data from controller |
| **No mass assignment** | `$model->fill($request->all())` without validation | Use FormRequest + explicit `$fillable` |

### 2.2 Frontend Prohibitions

| 🔴 Rule | What NOT to Do | Do This Instead |
|---------|----------------|-----------------|
| **No inline styles** | `<div style="border-radius: 12px; gap: 8px;">` | Create CSS class |
| **No inline onclick** | `<div onclick="window.location='...'">` | Use `data-href` + jQuery `.on()` |
| **No Bootstrap 5 classes** | `data-bs-toggle`, `me-2`, `ms-3` | Use BS4: `data-toggle`, `mr-2`, `ml-3` |
| **No CDN links for existing libs** | Adding new `<script src="https://cdn...">` | Check if already in `public/assets/` |
| **No duplicate IDs** | Multiple elements with same `id=""` | Use unique IDs or data attributes |

---

## 3. Libraries & Dependencies

### 3.1 Approved Libraries (Already Installed)

| Library | Composer / NPM | Use For |
|---------|-----------------|---------|
| `spatie/laravel-permission` | Composer | Roles & permissions — DO NOT replace |
| `yajra/laravel-datatables` | Composer | Server-side tables — standard for all lists |
| `barryvdh/laravel-dompdf` | Composer | PDF generation — use for report exports |
| `maatwebsite/excel` | Composer | Excel/CSV exports — use for bulk data |
| `intervention/image-laravel` | Composer | Image resize/process — use for uploads |
| `pusher/pusher-php-server` | Composer | Real-time notifications — keep using |
| `laravel-echo` + `pusher-js` | NPM | Frontend WebSocket listener |
| `laravel-vite-plugin` | NPM | Build tool — DO NOT replace with Mix |

### 3.2 Adding New Libraries

> [!IMPORTANT]
> Before adding any new library:
> 1. Check if existing libraries already handle the need
> 2. Prefer well-maintained, Laravel-native packages
> 3. Do NOT add frontend frameworks (React, Vue, Alpine) — this is a Blade + jQuery project
> 4. Do NOT add Tailwind CSS — this project uses vanilla CSS with a custom design system
> 5. AI agents must **ask for confirmation** before running `composer require` or `npm install`

---

## 4. Error Handling

### 4.1 Service Layer Error Handling

```php
// ✅ CORRECT — Service method with proper error handling
public function createClient(array $data): Client
{
    try {
        DB::beginTransaction();
        
        $client = $this->clientRepository->create($data);
        // ... business logic ...
        
        DB::commit();
        return $client;
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Client creation failed', [
            'data' => $data,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e; // Re-throw for controller to handle
    }
}
```

### 4.2 Controller Error Handling

```php
// ✅ CORRECT — Controller catches and returns appropriate response
public function store(ClientStoreRequest $request, ClientService $service)
{
    try {
        $client = $service->createClient($request->validated());
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    } catch (\Exception $e) {
        return back()->withInput()->with('error', 'Failed to create client. Please try again.');
    }
}

// ✅ CORRECT — AJAX endpoint returns JSON errors
public function ajaxStore(ClientStoreRequest $request, ClientService $service)
{
    try {
        $client = $service->createClient($request->validated());
        return response()->json(['success' => true, 'data' => $client]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

### 4.3 Error Handling Rules

| Rule | Description |
|------|-------------|
| **Log all exceptions** | Use `Log::error()` with context (user, input, trace) |
| **DB transactions** | Wrap multi-table writes in `DB::beginTransaction()` / `DB::commit()` |
| **User-friendly messages** | Never expose raw exception messages to users |
| **AJAX consistency** | All AJAX endpoints return `{ success: bool, data/message }` format |
| **Validation errors** | FormRequest handles automatically; return 422 for AJAX |
| **404 handling** | Use `findOrFail()` or `abort(404)` — never return null silently |

---

## 5. Boundaries for AI

### 5.1 What AI Agents MUST Do

| Rule | Description |
|------|-------------|
| **Read docs first** | Read MEMORY.md and RULES.md before making ANY changes |
| **Follow the architecture** | Controller → Service → Repository → Model |
| **Use existing CSS** | Check `erp-theme.css` and `erp-components.css` before creating new classes |
| **Match department colors** | Sales = Purple (#7F00FF), OD = Blue (#2E86DE), CSD = Teal (#10AC84) |
| **Update MEMORY.md** | After every significant change, update the "Currently Working On" section |
| **Preserve existing comments** | Don't remove comments or docblocks unrelated to the change |
| **Test after changes** | Verify the change works — check for errors, broken layouts |

### 5.2 What AI Agents MUST NOT Do

| Rule | Description |
|------|-------------|
| **No framework changes** | Do NOT introduce React, Vue, Alpine, Livewire, or Inertia |
| **No CSS framework changes** | Do NOT add Tailwind CSS or change the design system foundation |
| **No build tool changes** | Do NOT replace Vite or change the build pipeline |
| **No package upgrades** | Do NOT run `composer update` or `npm update` without explicit approval |
| **No database modifications** | Do NOT run raw SQL or modify DB without a migration file |
| **No file deletions** | Do NOT delete any files without explicit user approval |
| **No route changes** | Do NOT rename or remove existing routes (may break bookmarks/links) |
| **No auth changes** | Do NOT modify authentication or session handling |
| **No mass refactoring** | Do NOT rewrite entire files — make targeted, incremental changes |
| **No placeholder content** | Do NOT add Lorem Ipsum or fake data in views |

### 5.3 AI Behavior Rules

| Rule | Description |
|------|-------------|
| **Ask before big changes** | If a change touches 5+ files, present a plan first |
| **Explain non-obvious decisions** | Comment WHY, not WHAT, in code changes |
| **Keep file sizes small** | If a new file exceeds 300 lines, break it into smaller files |
| **Match existing patterns** | Look at CSD module as the reference architecture |
| **Use existing helpers** | Check `helper.php` and services before creating new utility functions |
| **Consistent naming** | Follow existing naming conventions (PascalCase controllers, camelCase methods) |

---

## 6. Code Quality Standards

### 6.1 Formatting

| What | Standard |
|------|----------|
| PHP indentation | 4 spaces |
| Blade indentation | 4 spaces |
| CSS indentation | 4 spaces |
| JS indentation | 4 spaces |
| Line ending | LF (Unix) |
| Max line length | 120 characters (soft limit) |

### 6.2 Naming Conventions

| What | Convention | Example |
|------|-----------|---------|
| Controllers | PascalCase + `Controller` suffix | `CsdRenewalController` |
| Services | PascalCase + `Service` suffix | `CsdRenewalService` |
| Repositories | PascalCase + `Repository` suffix | `ClientRepository` |
| FormRequests | PascalCase + `Request` suffix | `ClientStoreRequest` |
| Models | PascalCase (singular preferred) | `Client`, `Task`, `User` |
| Migrations | snake_case with timestamp | `2026_07_16_add_status_to_clients` |
| CSS classes | kebab-case or BEM | `erp-page-title`, `sales-metric-card--active` |
| Blade files | kebab-case | `team-report.blade.php` |
| Routes | dot notation | `csd.renewals.store` |

### 6.3 Commit Messages

```
[module] type: description

Examples:
[sales] feat: add bulk upload validation for CSV imports
[csd] fix: renewal date calculation off by one day
[design] refactor: extract admin KPI inline styles to CSS
[core] chore: remove unused imports from controllers
```
