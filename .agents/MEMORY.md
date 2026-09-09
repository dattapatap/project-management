# 🧠 Memory — What's Done, What's In Progress, What to Know

## Project: WMS — Workforce Management System / ERP

> **This file is the living memory of the project.**
> AI agents MUST read this file before making ANY changes.
> AI agents MUST update this file after EVERY significant change.
> Last Updated: **2026-07-16**

---

## 1. What Has Been Completed ✅

### 1.1 Modules Built & Operational

| Module | Status | Notes |
|--------|--------|-------|
| **NSD (Sales)** | ✅ Complete | Client CRUD, STS/DSR, lead allocation, bulk upload, pipeline kanban, activity calendar, handoff wizard, nudge system, leaderboard |
| **OD (Operations)** | ✅ Complete | Project management, task CRUD, task timer, task logs, operations calendar |
| **CSD (Customer Service)** | ✅ Complete | Client assignments, AMC contracts, renewals, support tickets, communications, change requests, collections, opportunities |
| **User Management** | ✅ Complete | CRUD, role/department/branch assignment, profile, password change |
| **Department/Team Mgmt** | ✅ Complete | Department CRUD, team creation, member assignment |
| **Day Closing** | ✅ Complete | Submission form, approval workflow, EOD reminder banner |
| **Daily Targets** | ✅ Complete | Admin target setting, per-user tracking |
| **Reports** | ✅ Complete | STS reports, DSR reports, sales reports, employee reports, operations reports, CSD team reports |
| **Notifications** | ✅ Complete | Real-time via Pusher, notification center |
| **Auth** | ✅ Complete | Login, register, password reset (Laravel UI) |
| **Dashboards** | ✅ Complete | 9 role-specific dashboards (Admin, BM, Sales TL, Sales Exec, OD TL, OD Employee, PM, CSD TL, CSD Exec) |
| **Multi-Branch** | ✅ Complete | Branch scoping via `BranchScopeService` |

### 1.2 Documentation Created

| File | Status | Date |
|------|--------|------|
| `.agents/AGENTS.md` | ✅ Created | 2026-07-16 |
| `.agents/PRD.md` | ✅ Created | 2026-07-16 |
| `.agents/ARCHITECTURE.md` | ✅ Created | 2026-07-16 |
| `.agents/RULES.md` | ✅ Created | 2026-07-16 |
| `.agents/DESIGN.md` | ✅ Created | 2026-07-16 |
| `.agents/PHASES.md` | ✅ Created | 2026-07-16 |
| `.agents/MEMORY.md` | ✅ Created | 2026-07-16 |

### 1.3 CSS / Design System

| File | Status | Notes |
|------|--------|-------|
| `public/css/erp-theme.css` (21KB) | ✅ Exists | CSS variables, font scale, layout tokens |
| `public/css/erp-components.css` (50KB) | ✅ Exists | Component library — cards, badges, tables, page headers |
| `public/css/sales-dashboard.css` (17KB) | ✅ Exists | Sales-specific KPI cards, metric badges |
| `public/css/reports.css` (6KB) | ✅ Exists | Report table styles |
| `public/css/auth.css` (11KB) | ✅ Exists | Login/register page design |

---

## 2. Currently Working On 🔄

> **Update this section every time you start working on a file or feature.**

| What | File(s) Being Modified | Status | Agent/Date |
|------|----------------------|--------|------------|
| Project documentation setup | `.agents/*.md` | ✅ Done | 2026-07-16 |
| Daily targets tracker, approvals fixes, live activity widget, and team performance scroll/active filter | `app/Http/Controllers/DailyTargetController.php`, `app/Http/Controllers/DailyClosingController.php`, `resources/views/components/day-closing/index.blade.php`, `resources/views/components/day-closing/approvals.blade.php`, `resources/views/components/targets/daily-targets.blade.php`, `app/Exports/DailyTargetsExport.php`, `routes/web.php`, `app/Services/EmployeeStatusDashboardService.php`, `resources/views/dashboards/widgets/employee_status.blade.php`, `app/Providers/AppServiceProvider.php`, `resources/views/dashboards/admin_ui.blade.php`, `resources/views/dashboards/branch_manager_ui.blade.php`, `resources/views/dashboards/tl_sales_ui.blade.php`, `resources/views/dashboards/tl_od_ui.blade.php`, `resources/views/dashboards/tl_csd_ui.blade.php`, `app/Services/BranchScopeService.php`, `app/Http/Controllers/HomeController.php` | ✅ Done | Antigravity / 2026-07-16 |
| Decouple Live Tasks to active running timers & fix project name lookup | `app/Services/EmployeeStatusDashboardService.php` | ✅ Done | Antigravity / 2026-07-16 |
| Attendance per-day records filter fix & Branch Manager access enablement | `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/components/administration/attendances/index.blade.php`, `routes/web.php`, `app/Exports/AdminAttendancesExport.php`, `app/Exports/DateRangeAttendanceExport.php`, `app/Http/Middleware/PreventRequestsDuringMaintenance.php` | ✅ Done | Antigravity / 2026-09-09 |

---

## 3. Model Status Tracker 📊

> Track which models have been reviewed, fixed, or need work.

### 3.1 User & Access Models

| Model | File | Has `$fillable` | Has `$casts` | Has Return Types | Status |
|-------|------|:---:|:---:|:---:|--------|
| `User` | `app/Models/User.php` | ✅ | ✅ | Partial | ⚠️ Needs review |
| `Role` | `app/Models/Role.php` | ❌ | ❌ | ❌ | ⚠️ Thin wrapper |
| `Employees` | `app/Models/Employees.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `Department` | `app/Models/Department.php` | ❌ | ❌ | ✅ | ⚠️ Needs `$fillable` |
| `DepartmentMember` | `app/Models/DepartmentMember.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `UserDepartment` | `app/Models/UserDepartment.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `UserBranch` | `app/Models/UserBranch.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `Branches` | `app/Models/Branches.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `Teams` | `app/Models/Teams.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `TeamMembers` | `app/Models/TeamMembers.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `UserActivity` | `app/Models/UserActivity.php` | ✅ | ❌ | ❌ | ⚠️ Needs `$casts` |

### 3.2 Sales / NSD Models

| Model | File | Has `$fillable` | Has `$casts` | Has Return Types | Status |
|-------|------|:---:|:---:|:---:|--------|
| `Clients` | `app/Models/Clients.php` | ❌ `$guarded=[]` | ❌ | ✅ | 🔴 Needs `$fillable` |
| `ClientHistory` | `app/Models/ClientHistory.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `ClientDocs` | `app/Models/ClientDocs.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `ClientDomains` | `app/Models/ClientDomains.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `ClientPackages` | `app/Models/ClientPackages.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `ClientPayments` | `app/Models/ClientPayments.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `ParentStatus` | `app/Models/ParentStatus.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `SalesTarget` | `app/Models/SalesTarget.php` | ✅ | ❌ | ❌ | ⚠️ Needs `$casts` |
| `DailyTarget` | `app/Models/DailyTarget.php` | ✅ | ✅ | ❌ | ✅ OK |
| `ServiceCatalog` | `app/Models/ServiceCatalog.php` | ✅ | ❌ | ❌ | ⚠️ Needs `$casts` |

### 3.3 Operations / OD Models

| Model | File | Has `$fillable` | Has `$casts` | Has Return Types | Status |
|-------|------|:---:|:---:|:---:|--------|
| `DepartmentProjects` | `app/Models/DepartmentProjects.php` | ❌ `$guarded=[]` | ❌ | Partial | 🔴 Needs `$fillable` |
| `DepartmentProjectHistory` | `app/Models/DepartmentProjectHistory.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `ProjectCategory` | `app/Models/ProjectCategory.php` | ✅ | ✅ | ✅ | ✅ OK |
| `ProjectSubCategory` | `app/Models/ProjectSubCategory.php` | ✅ | ✅ | ✅ | ✅ OK |
| `Task` | `app/Models/Task.php` | ❌ `$guarded=[]` | ❌ | Partial | 🔴 Needs `$fillable` |
| `TaskComment` | `app/Models/TaskComment.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `TaskLog` | `app/Models/TaskLog.php` | ❌ | ❌ | ❌ | ⚠️ Needs review |
| `TeamProject` | `app/Models/TeamProject.php` | ✅ | ❌ | ❌ | ⚠️ Needs `$casts` |
| `Document` | `app/Models/Document.php` | ✅ | ❌ | ❌ | ⚠️ Needs `$casts` |
| `GlobalAttendanceLog` | `app/Models/GlobalAttendanceLog.php` | ✅ | ✅ | ❌ | ✅ OK |
| `DayClosing` | `app/Models/DayClosing.php` | ✅ | ✅ | ❌ | ✅ OK |

### 3.4 CSD Models

| Model | File | Has `$fillable` | Has `$casts` | Has Return Types | Status |
|-------|------|:---:|:---:|:---:|--------|
| `CsdClientAssignment` | `app/Models/CsdClientAssignment.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdAmcContract` | `app/Models/CsdAmcContract.php` | ❌ `$guarded=[]` | ✅ | ✅ | 🔴 Needs `$fillable` |
| `CsdRenewal` | `app/Models/CsdRenewal.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdSupportTicket` | `app/Models/CsdSupportTicket.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdChangeRequest` | `app/Models/CsdChangeRequest.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdCommunication` | `app/Models/CsdCommunication.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdCollectionFollowup` | `app/Models/CsdCollectionFollowup.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdContactPerson` | `app/Models/CsdContactPerson.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |
| `CsdOpportunity` | `app/Models/CsdOpportunity.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |

### 3.5 Commercial / Engagement Models

| Model | File | Has `$fillable` | Has `$casts` | Has Return Types | Status |
|-------|------|:---:|:---:|:---:|--------|
| `ClientEngagement` | `app/Models/ClientEngagement.php` | ❌ `$guarded=[]` | ✅ | ✅ | 🔴 Needs `$fillable` |
| `ClientEngagementEvent` | `app/Models/ClientEngagementEvent.php` | ❌ `$guarded=[]` | ❌ | ❌ | 🔴 Needs `$fillable` |

### Summary: Model Health

| Status | Count | Action |
|--------|-------|--------|
| 🔴 Has `$guarded = []` (needs `$fillable`) | **16 models** | Phase 1 priority |
| ⚠️ Missing `$fillable` or `$casts` | **~15 models** | Phase 4 |
| ✅ OK (has `$fillable` + `$casts`) | **~4 models** | No action needed |
| ❌ Not reviewed | **~8 models** | Review needed |

---

## 4. Key Gotchas & Pitfalls ⚠️

### 4.1 Bootstrap Version Conflict
- **`package.json`** has Bootstrap 5, but the **actual UI uses Bootstrap 4** classes
- Use `data-toggle` (NOT `data-bs-toggle`), `mr-*` (NOT `me-*`), `ml-*` (NOT `ms-*`)
- The BS4 CSS is loaded from `public/assets/css/bootstrap.min.css`

### 4.2 Department ID Magic Numbers
- `1` = NSD (Sales), `2` = OD (Operations), `3` = CSD (Customer Service)
- Hardcoded in: `HomeController`, `SalesDashboardController`, `home.blade.php`, middleware, views
- **Fix planned:** Create `App\Enums\Department` constants (Phase 4)

### 4.3 Client Field Meanings
- `ref_user` = Sales Executive who **manages** the lead
- `tele_ref_user` = Team Leader who **assigned/oversees** the lead
- `created_by` = User who **created** the record (may differ in bulk uploads)

### 4.4 User-Department is `hasOne`
```php
$user->departments->department  // Returns department ID (integer, not model)
```

### 4.5 Team Member Resolution Pattern
To find all team members for a TL:
```php
$teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team');
$members = TeamMembers::whereIn('team', $teams)->where('status', true)->pluck('user');
```
This is **duplicated in 10+ places** — needs centralization.

### 4.6 Branch Manager Detection
- `$user->isBranchManager()` is a **model method**, not a Spatie role check
- Separate from `$user->hasRole('Branch-Manager')`

### 4.7 Client URL Encoding
- Client detail URLs use `base64_encode($client->id)` — this is obfuscation, NOT security
- Example: `/clients/{base64id}/sts`

### 4.8 Day Closing Check
- Trigger: `Carbon::now()->hour >= 18` (after 6 PM)
- Checked in both `app.blade.php` layout AND `HomeController`
- Shared via `view()->share('hasSubmittedClosingToday', $bool)`

### 4.9 Public SQL Dumps (SECURITY RISK)
- `public/db_erp_digitalnock.sql` (12.8MB) — publicly accessible
- `public/db_erp_digitalnock_final.sql` (13.4MB) — publicly accessible
- **Must delete immediately** (Phase 1, Step 1.1)

---

## 5. Environment & Setup

### 5.1 Required `.env` Values
```env
APP_NAME=Digitalnock
APP_URL=http://localhost
DB_DATABASE=erp_digitalnock
PUSHER_APP_ID=xxx
PUSHER_APP_KEY=xxx
PUSHER_APP_SECRET=xxx
PUSHER_APP_CLUSTER=ap2
```

### 5.2 Dev Setup Commands
```bash
composer install
npm install
php artisan migrate
php artisan db:seed
npm run dev          # Vite HMR (localhost:5173)
php artisan serve    # OR use Herd/Valet
```

### 5.3 Vite Config Notes
- Auto-detects Herd/Valet TLS for `*.test` domains
- HMR on `localhost:5173` for standard setup
- Hot-reloads: views, CSS, JS, routes, PHP

---

## 6. Third-Party Integrations

| Service | Package | Config |
|---------|---------|--------|
| Pusher (Real-time) | `pusher/pusher-php-server` | `config/broadcasting.php` |
| Mail | Laravel Mail | `config/mail.php` |
| PDF (Reports) | `barryvdh/laravel-dompdf` | `config/dompdf.php` |
| Excel Export | `maatwebsite/excel` | `config/excel.php` |
| Image Processing | `intervention/image-laravel` | `config/image.php` |
| QR Codes | `simplesoftwareio/simple-qrcode` | — |
| Avatars | `laravolt/avatar` | `config/laravolt/` |
| Chunked Upload | `pion/laravel-chunk-upload` | `config/chunk-upload.php` |

---

## 7. Change Log

> **Update this section after every significant change.**

| Date | What Changed | Files Modified | Author |
|------|-------------|---------------|--------|
| 2026-07-16 | Full project audit + documentation suite created | `.agents/*.md` (7 files) | AI Agent |
| 2026-07-16 | Fixed targets display, remarks limit, formatted checklist, added export, live activity widget, active user scope, and script syntax fix | `app/Http/Controllers/DailyTargetController.php`, `app/Http/Controllers/DailyClosingController.php`, `resources/views/components/day-closing/index.blade.php`, `resources/views/components/day-closing/approvals.blade.php`, `resources/views/components/targets/daily-targets.blade.php`, `app/Exports/DailyTargetsExport.php`, `routes/web.php`, `app/Services/EmployeeStatusDashboardService.php`, `resources/views/dashboards/widgets/employee_status.blade.php`, `app/Providers/AppServiceProvider.php`, `resources/views/dashboards/admin_ui.blade.php`, `resources/views/dashboards/branch_manager_ui.blade.php`, `resources/views/dashboards/tl_sales_ui.blade.php`, `resources/views/dashboards/tl_od_ui.blade.php`, `resources/views/dashboards/tl_csd_ui.blade.php`, `app/Services/BranchScopeService.php`, `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/tl_od_scripts.blade.php` | Antigravity |
| 2026-07-16 | Decoupled Live Tasks to active running timers & fixed project_name lookup | `app/Services/EmployeeStatusDashboardService.php` | Antigravity |
| 2026-08-22 | Enhanced real-time notification popup with explicit Cancel and Open actions to prevent form data loss when creating tasks | `resources/views/layouts/app.blade.php` | Antigravity |
| 2026-08-22 | Added Tasks sidebar item & role-specific tasks list view for Admin, Manager, and Team Leader with My Tasks vs Team Members tabs, role-scoped filters, DateRangePicker, colorful status-themed KPI cards, 50/page pagination, and Details action | `resources/views/layouts/partials/sidebar.blade.php`, `resources/views/components/tasks/index.blade.php`, `app/Http/Controllers/TaskController.php`, `app/Services/Od/TaskService.php`, `app/Repositories/TaskRepository.php`, `routes/project.php` | Antigravity |
| 2026-08-22 | Added Project Categories & Sub-Categories CRUD management module in Administration for Admin and Branch Manager with parent category linking and duplicate prevention rules | `app/Models/ProjectCategory.php`, `app/Models/ProjectSubCategory.php`, `app/Services/Administration/ProjectCategoryService.php`, `app/Http/Controllers/ProjectCategoryController.php`, `routes/web.php`, `resources/views/layouts/partials/sidebar.blade.php`, `resources/views/components/administration/project-categories/index.blade.php` | Antigravity |
| 2026-08-22 | Updated Daily Targets tracker to show day name below date, exclude Sunday records, and added Apply & Reset filter buttons | `app/Http/Controllers/DailyTargetController.php`, `app/Exports/DailyTargetsExport.php`, `resources/views/components/targets/daily-targets.blade.php` | Antigravity |
| 2026-08-22 | Refactored Day Closing Approvals board to display only submitted closings in Daily Audit Checklist and only active unsubmitted employees in Pending Submissions table | `app/Http/Controllers/DailyClosingController.php`, `resources/views/components/day-closing/approvals.blade.php` | Antigravity |
| 2026-08-22 | Redesigned Employee Report (/reports/employees) with unified Date Range Picker, Department filter, corporate KPI summary cards, Velocity trend chart, and enterprise Performance Matrix table | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employees.blade.php` | Antigravity |
| 2026-08-22 | Enhanced Individual Employee Report (/reports/employee/{id}) with live working activity status, average daily working/task hours, missing day closing counter (excluding Sundays), task-driven performance score, day-by-day task spend audit, and red highlight on maximum time consuming task | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Fixed DataTables Ajax reload handling on filter button and restored performanceScore definition in EmployeeReportController | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employees.blade.php` | Antigravity |
| 2026-08-22 | Fixed undefined phone attribute check in employee_detail.blade.php by safely accessing emp relationship | `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Strictly bound Individual Employee Report (/reports/employee/{id}) metrics and top KPI cards to the exact selected date filter and employee hiring date | `app/Http/Controllers/Reports/EmployeeReportController.php` | Antigravity |
| 2026-08-22 | Excluded Sundays completely from Daily Work Rhythm audit table, capped range strictly up to today, and added DataTables pagination (15/page) | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Enhanced Task Time Allocation & Consumption table to calculate task time frames (10:00 AM - 6:30 PM shift, Sundays excluded), compare actual hours vs allotted duration, and highlight overtime/exceeded tasks | `app/Services/Reports/OdWorkReportService.php`, `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Added explicit Apply and Reset filter buttons with responsive daterangepicker controls to the Individual Employee Report (/reports/employee/{id}) | `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Redesigned Operations Report (/reports/operations) with unified DateRangePicker, Department dropdown filter, 4 corporate KPI stat cards, and enterprise Performance Matrix DataTables | `app/Http/Controllers/Reports/OperationsReportController.php`, `resources/views/components/reports/operations.blade.php` | Antigravity |
| 2026-08-22 | Fixed card spacing, inner padding, number alignment, and case-insensitive task statuses in Operations Report | `resources/views/components/reports/operations.blade.php`, `app/Services/Reports/OdWorkReportService.php` | Antigravity |
| 2026-08-22 | Updated Days Worked to calculate strictly from shift attendance (GlobalAttendanceLog) and Avg Daily Hours strictly as Task Hours / Shift Days Worked | `app/Services/Reports/OdWorkReportService.php` | Antigravity |
| 2026-08-22 | Converted all decimal hour metrics into time-based format (e.g. 15.45 hrs for 15h 45m instead of decimal 15.75) across Operations and Employee reports | `app/Services/Reports/OdWorkReportService.php`, `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employee_detail.blade.php`, `resources/views/components/reports/operations.blade.php` | Antigravity |
| 2026-08-22 | Created global format_timing_hours helper in app/Helper/helper.php and applied timing format to all reports and PDF exports | `app/Helper/helper.php`, `resources/views/components/reports/employees.blade.php`, `resources/views/components/reports/employee_detail_pdf.blade.php` | Antigravity |
| 2026-08-22 | Fixed spacing between avatar icon bubble and employee name/ID on Operations Report (/reports/operations) | `resources/views/components/reports/operations.blade.php` | Antigravity |
| 2026-08-22 | Redesigned Daily Targets Tracker (/daily-targets) with executive UI, time-formatted parameters, and dynamic Missed Submissions Audit KPI cards with exact missed date badges when filtering specific users | `app/Http/Controllers/DailyTargetController.php`, `resources/views/components/targets/daily-targets.blade.php` | Antigravity |
| 2026-08-22 | Added 10-badge limit and collapsible toggle button (+X More Missed Days / Show Less) on Daily Targets Tracker to prevent UI clutter on large date ranges | `resources/views/components/targets/daily-targets.blade.php` | Antigravity |
| 2026-08-22 | Added interactive Day Task Details modal and click handler on Daily Work Rhythm & Task Log Audit table showing task titles, client names, project names, time spent, and work notes | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Fixed Day Task Details popup modal close buttons with explicit jQuery dismiss handler and data attributes | `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Enhanced Task Details popup modal to prominently display Task Name, Project Name, Client Name, and Work Log Description | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Fixed unescaped HTML attribute JSON serialization bug by utilizing centralized @json JavaScript object lookup with data-date keys on Daily Work Rhythm table | `app/Http/Controllers/Reports/EmployeeReportController.php`, `resources/views/components/reports/employee_detail.blade.php` | Antigravity |
| 2026-08-22 | Removed invalid withTrashed call on non-softdelete Task BelongsTo relation in EmployeeReportController | `app/Http/Controllers/Reports/EmployeeReportController.php` | Antigravity |
| 2026-08-27 | Added Admin-Only Attendances & Live Workforce Monitoring module (/admin/attendances) under Day Closing with live activity dots, current active project/task, shift window, timing hours, missed day closing audit rule (excluding shift hours on missed days), and auto-closing orphan logs on shift start | `routes/web.php`, `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/layouts/partials/sidebar.blade.php`, `resources/views/components/administration/attendances/index.blade.php`, `app/Services/Od/GlobalTimerService.php` | Antigravity |
| 2026-08-27 | Enforced mandatory daily shift before moving tasks to In Progress or starting task timer, auto-resumed InProgress task on shift start, and zeroed unclosed overnight orphan timer hours without 9PM inflation | `app/Services/Od/TaskService.php`, `app/Services/Od/GlobalTimerService.php` | Antigravity |
| 2026-08-27 | Updated shift cap from 9:00 PM to 11:00 PM (23:00), added daily scheduled attendance:nightly-closing-audit command to nullify unclosed running timers at 11:00 PM, and excluded Admin from Attendances board | `app/Services/Od/TaskService.php`, `app/Services/Od/GlobalTimerService.php`, `app/Services/DailyClosingService.php`, `app/Console/Commands/NightlyAttendanceClosingAudit.php`, `app/Console/Kernel.php`, `app/Http/Controllers/Admin/AdminAttendanceController.php` | Antigravity |
| 2026-08-27 | Added Shift vs Task Productivity Efficiency Ratio (%), Late Clock-in & Early Departure detection, Break Duration & Over-Break alerts, One-Click Excel Payroll Attendance Export, and 60-second silent Live Auto-Refresh on Admin Dashboard & Attendances page | `app/Exports/AdminAttendancesExport.php`, `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/components/administration/attendances/index.blade.php`, `resources/views/dashboards/admin_ui.blade.php`, `routes/web.php` | Antigravity |
| 2026-08-27 | Fixed DataTables Ajax error by properly importing GlobalTimerService namespace and excluded Sundays completely from Absent and Missed Day Closing counters on Attendances board | `app/Http/Controllers/Admin/AdminAttendanceController.php`, `app/Exports/AdminAttendancesExport.php` | Antigravity |
| 2026-08-27 | Removed standalone DEPT column from Attendances table, embedded department badges inside Employee name cell, and enabled full cross-device horizontal responsiveness | `resources/views/components/administration/attendances/index.blade.php` | Antigravity |
| 2026-08-27 | Consolidated Attendances table into a high-density 6-column single-screen layout (Employee & Activity, Active Project & Task, Shift & Punctuality, Hours & Break, Productivity & Closing, Action) with 100% width and zero horizontal scrollbar | `resources/views/components/administration/attendances/index.blade.php` | Antigravity |
| 2026-08-27 | Built complete Settings and HRMS suite for Admin & Branch Manager: Holidays Management, Leave Types & annual quotas, Project Categories, Date-Range Attendance Export (.xlsx), Employee Leave Application portal, and Admin/BM Leave Approvals | `app/Models/Holiday.php`, `app/Models/LeaveType.php`, `app/Models/EmployeeLeave.php`, `app/Services/Hrms/HolidayService.php`, `app/Services/Hrms/LeaveService.php`, `app/Exports/DateRangeAttendanceExport.php`, `app/Http/Controllers/Settings/*`, `app/Http/Controllers/Hrms/*`, `resources/views/components/settings/*`, `resources/views/components/hrms/*`, `routes/web.php`, `resources/views/layouts/partials/sidebar.blade.php` | Antigravity |
| 2026-08-27 | Added 2 EL (Earned Leave) per month rule with monthly limits and monthly accrual enforcement in LeaveService, LeaveTypeController, and HRMS views | `database/migrations/2026_08_27_130300_add_monthly_limits_to_leave_types_table.php`, `app/Models/LeaveType.php`, `app/Services/Hrms/LeaveService.php`, `app/Http/Controllers/Settings/LeaveTypeController.php`, `resources/views/components/settings/leave_types/index.blade.php`, `resources/views/components/hrms/leaves/index.blade.php` | Antigravity |
| 2026-08-27 | Moved Attendances board under the HRMS tab exclusively for Admin and organized Day Closing, HRMS, and Settings tabs | `resources/views/layouts/partials/sidebar.blade.php` | Antigravity |
| 2026-08-27 | Removed the top header "Add Task" shortcut button for Admin users | `resources/views/layouts/app.blade.php` | Antigravity |
| 2026-08-27 | Removed Project Categories link from Administration / Users & Teams menus (consolidated under Settings) | `resources/views/layouts/partials/sidebar.blade.php` | Antigravity |
| 2026-08-27 | Relocated Export Attendance routes from /settings/attendance-export to /hrms/attendance-export and namespaced under App\Http\Controllers\Hrms | `routes/web.php`, `app/Http/Controllers/Hrms/AttendanceExportController.php`, `resources/views/components/hrms/attendance_export/index.blade.php`, `resources/views/layouts/partials/sidebar.blade.php` | Antigravity |
| 2026-08-27 | Added dedicated Attendance Status column (Present, Absent, Half Day, Approved Leave, Holiday, Sunday) to the Attendances table | `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/components/administration/attendances/index.blade.php` | Antigravity |
| 2026-08-27 | Modularized and optimized sidebar navigation into dedicated role-based views (admin-menu, nsd-menu, od-menu, csd-menu) | `resources/views/layouts/partials/sidebar.blade.php`, `resources/views/layouts/partials/sidebar/*` | Antigravity |
| 2026-08-27 | Removed Daily Targets navigation item from the Operations (OD) department sidebar menu | `resources/views/layouts/partials/sidebar/od-menu.blade.php` | Antigravity |
| 2026-08-27 | Moved My Leaves under a dedicated HRMS section towards the bottom across all departmental sidebar menus (OD, NSD, CSD) | `resources/views/layouts/partials/sidebar/*` | Antigravity |
| 2026-08-27 | Implemented pixel-perfect Executive Admin Dashboard exactly matching user reference design (Won Deals, Leads, Active Projects, Customers, Employees KPI cards with sparklines, Needs Your Attention & Today's Priorities action panels, NSD/OD/CSD 3-department overview with funnel, donuts, deadlines, live employee attendance mini-table, top performers tabs, and recent activity feed) | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php`, `resources/views/dashboards/admin_scripts.blade.php` | Antigravity |
| 2026-08-27 | Enhanced dashboard UI card paddings and implemented 5-minute backend caching in HomeController to optimize load performance | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Enhanced OD Top Performers on Admin Dashboard to rank and display both completed tasks count and total logged task hours | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Implemented top date range selector with quick presets, verified deep linking across all cards/alerts, and added real-time Employee Work Status details (active running task title, punctuality badges, shift and task hours) | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Redesigned Top Performers card to expand to Top 5 ranks with Monthly MVP Spotlight ribbons, employee avatars, designations, detailed metric pills, and balanced full-height layout | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Fixed floating-point deadline countdown bug in OD Overview, expanded upcoming deadlines & at-risk client feeds to 5 items, added summary insight footers, and eliminated empty card space across NSD, OD, and CSD cards | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Cleaned deadline countdown format (Due Today, Due Tomorrow, In X days, Overdue Xd), removed all hardcoded dummy fallback data across CSD & Recent Activities to show true database state, and cleared application cache | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Imported Illuminate\Support\Str in HomeController to fix Class 'App\Http\Controllers\Str' not found exception | `app/Http/Controllers/HomeController.php` | Antigravity |
| 2026-08-27 | Fixed date range filtering on Admin Dashboard to dynamically filter all metrics (attendance, employee status table, won deals, leads, tasks, top performers) based on preset (today, yesterday, this_week, this_month) or custom date | `app/Http/Controllers/HomeController.php`, `resources/views/dashboards/admin_ui.blade.php` | Antigravity |
| 2026-08-27 | Fixed column references from non-existent 'first_in'/'last_out' to 'starttime'/'endtime' on GlobalAttendanceLog in HomeController | `app/Http/Controllers/HomeController.php` | Antigravity |
| 2026-08-27 | Updated Daily Targets & Closing Audit table to remove benchmark denominators (7h, 6h, 1) and show only the calculated hours and tasks performed | `app/Http/Controllers/DailyTargetController.php` | Antigravity |
| 2026-08-27 | Updated user Day Closing Submission view to remove target benchmark labels and display only calculated hours, tasks, touchpoints, and work metrics | `resources/views/components/day-closing/index.blade.php` | Antigravity |
| 2026-08-27 | Updated Executive Daily Remarks length constraint to strictly require between 10 and 30 words across backend validation and frontend JS | `app/Http/Controllers/DailyClosingController.php`, `resources/views/components/day-closing/index.blade.php` | Antigravity |
| 2026-08-27 | Updated DailyClosingService and HomeController to dynamically include the elapsed time of active running tasks up to current moment in Task Work Time calculations | `app/Services/DailyClosingService.php`, `app/Http/Controllers/HomeController.php` | Antigravity |
| 2026-08-27 | Completed comprehensive pre-production audit across all controllers, blade templates, routes, models, and calculation services; all syntax, blade views, and routes passed with 0 errors | Global Codebase | Antigravity |
| 2026-08-27 | Created standalone public/maintenance.html and updated resources/views/errors/503.blade.php for seamless production deployment maintenance mode | `public/maintenance.html`, `resources/views/errors/503.blade.php` | Antigravity |
| 2026-08-27 | Removed public/maintenance.html and streamlined maintenance mode to exclusively use resources/views/maintenance.blade.php via resources/views/errors/503.blade.php | `resources/views/errors/503.blade.php` | Antigravity |
| 2026-08-27 | Added web-based /down, /up, /migrate, /storage-link, and /cache-clear endpoints with middleware bypass exceptions for seamless shared hosting administration | `routes/web.php`, `app/Http/Middleware/PreventRequestsDuringMaintenance.php` | Antigravity |
| 2026-08-27 | Restored PreventRequestsDuringMaintenance middleware with whitelist exceptions and streamlined /up and /down routes for shared hosting | `app/Http/Middleware/PreventRequestsDuringMaintenance.php`, `routes/web.php` | Antigravity |
| 2026-08-27 | Created standalone public/down.php and public/up.php direct PHP scripts to guarantee 100% reliable maintenance control on shared hosting without Laravel routing dependency | `public/down.php`, `public/up.php` | Antigravity |
| 2026-08-27 | Enhanced Workforce Attendance & Monitoring module: added interactive Date Range Picker with presets, converted Late/Early punctuality to proper hours/minutes format, updated Not Clocked In badges to danger styling, and added descriptive Idle (On Shift) indicators | `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/components/administration/attendances/index.blade.php`, `app/Exports/AdminAttendancesExport.php` | Antigravity |
| 2026-08-27 | Fixed Attendance Excel Export: resolved DayClosing date format mismatch bug that zeroed Verified Shift Hours and Efficiency Ratio, removed Department & Designation columns, separated Shift Start and Shift Close (empty when open), and reordered Attendance Status before Projects Worked On | `app/Exports/DateRangeAttendanceExport.php`, `app/Exports/AdminAttendancesExport.php` | Antigravity |
| 2026-08-27 | Added approved user name display across Daily Targets matrix, Day Closing Approvals list, Executive Closing History, and Workforce Attendance Monitoring table | `app/Http/Controllers/DailyTargetController.php`, `resources/views/components/day-closing/approvals.blade.php`, `resources/views/components/day-closing/index.blade.php`, `resources/views/components/administration/attendances/index.blade.php`, `app/Http/Controllers/Admin/AdminAttendanceController.php` | Antigravity |
| 2026-08-27 | Implemented strict 2-day back approval and audit restriction for Team Leaders (Today, Yesterday, Day Before Yesterday) on Day Closing Approvals while leaving Branch Managers and Admins unrestricted | `app/Http/Controllers/DailyClosingController.php`, `resources/views/components/day-closing/approvals.blade.php` | Antigravity |
| 2026-08-27 | Formatted Projects Worked On column in Attendance Excel Export to display Company Name - Project Name (e.g. Aurua Med Tours - Static Website, Interwood Kitchen - CRM) | `app/Exports/DateRangeAttendanceExport.php` | Antigravity |
| 2026-08-31 | Updated Day Closing Executive Remarks word limit constraints to allow up to 80 words across backend validation and frontend JS form checks | `app/Http/Controllers/DailyClosingController.php`, `resources/views/components/day-closing/index.blade.php` | Antigravity |
| 2026-08-31 | Updated task delegation ('Delegate' checkbox) to display both Team Leaders and all other team members/employees in the department grouped by their team | `app/Services/Od/ProjectService.php`, `app/Http/Controllers/TaskController.php` | Antigravity |
| 2026-09-09 | Enhanced Attendances module: enabled full access for Branch Managers with branch scoping, added Date column, and refactored date range filtering to list separate day-by-day records for each employee instead of bundled single-row aggregates | `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/components/administration/attendances/index.blade.php`, `routes/web.php`, `app/Exports/AdminAttendancesExport.php`, `app/Exports/DateRangeAttendanceExport.php`, `app/Http/Middleware/PreventRequestsDuringMaintenance.php` | Antigravity |
| 2026-09-09 | Added interactive deep linking to Project Taskboards across Active Project & Task table cells and Daily Task Breakdown & Time Logs modal popup cards | `app/Http/Controllers/Admin/AdminAttendanceController.php`, `resources/views/components/administration/attendances/index.blade.php` | Antigravity |
| 2026-09-09 | Updated Task Information & Details page (/projects/task/{taskid}/history) to display explicit full Date & Time (d M Y, h:i A) across Timeline, Actual Start, Completed On, Created By, Activity Log, and Discussion comments (avoiding relative 'hours ago') | `resources/views/components/projects/taskdetails.blade.php` | Antigravity |
| 2026-09-09 | Enhanced Taskboard page (/projects/taskboard/{id}) with Project Summary banner (Client, Category, Start/Due Timeline, Total Tasks breakdown, Working Devs avatar stack, Total Time Logged) and upgraded Project Details page (/projects/{id}/history) with Timeline Performance analysis (Completed Under/Over Timeline vs On Track/Overdue), Total Time Spent metrics, and Involved Developers contribution breakdown table | `app/Models/DepartmentProjects.php`, `app/Http/Controllers/TaskController.php`, `resources/views/components/projects/tasksbar.blade.php`, `resources/views/projects/history.blade.php` | Antigravity |
| 2026-09-09 | Fixed Branch Manager Attendances access: removed `@if($isAdmin)` sidebar visibility check in HRMS menu and safeguarded branch ID resolution in AdminAttendanceController and DateRangeAttendanceExport | `resources/views/layouts/partials/sidebar/admin-menu.blade.php`, `app/Http/Controllers/Admin/AdminAttendanceController.php`, `app/Exports/DateRangeAttendanceExport.php` | Antigravity |

