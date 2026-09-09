# 🏗️ Architecture Document

## Project: WMS — Workforce Management System / ERP

---

## 1. App Flow & Architecture

### 1.1 High-Level Request Flow

```
[Browser] ──HTTP──▶ [Routes (web.php)] ──▶ [Middleware (Auth, RBAC, Department)] ──▶ [Controller]
                                                                                        │
                                           ┌────────────────────────────────────────────┤
                                           │                                            │
                                     [Service Layer]                            [FormRequest]
                                     (Business Logic)                           (Validation)
                                           │
                                     [Repository Layer]
                                     (Data Access)
                                           │
                                     [Eloquent Models]
                                           │
                                     [MySQL Database]
                                           │
                     ┌─────────────────────┤
                     ▼                     ▼
               [Blade View]        [JSON Response]
               (HTML Page)         (DataTables/AJAX)
```

### 1.2 Authentication Flow
```
Guest → Login Page (authlayout.blade.php)
  │
  ├── POST /login → Laravel Auth (Session-based)
  │
  └── Authenticated → /home → HomeController::index()
       │
       ├── Admin?          → AdminDashboardData   → admin_ui.blade.php
       ├── Branch Manager? → BranchManagerDashCtrl → branch_manager_ui.blade.php
       ├── Sales TL?       → SalesDashboardCtrl   → tl_sales_ui + sales_ui.blade.php
       ├── Sales Exec?     → SalesDashboardCtrl   → sales_ui.blade.php
       ├── OD TL?          → HomeCtrl methods      → tl_od_ui + employee_od_ui.blade.php
       ├── OD Employee?    → HomeCtrl methods      → employee_od_ui.blade.php
       ├── Project Mgr?    → HomeCtrl methods      → pm_ui.blade.php
       ├── CSD TL?         → CsdDashboardCtrl     → tl_csd_ui.blade.php
       └── CSD Exec?       → CsdDashboardCtrl     → csd_ui.blade.php
```

### 1.3 Client Lifecycle Flow (Cross-Department)
```
[Lead Created by Sales]
       │
       ▼
  Fresh → Followup → Meeting Fixed → Hot/Warm Perspective → Matured
                                                                │
                 ┌──────────────────────────────────────────────┤
                 │                                              │
                 ▼                                              ▼
       [Project Created in OD]                    [CSD Assignment Created]
       DepartmentProject                          CsdClientAssignment
                 │                                              │
                 ▼                                              ▼
       [Tasks Assigned]                           [Renewals / AMC / Support]
       Task → TaskLog → TaskComment               CsdRenewal, CsdAmcContract
```

### 1.4 Middleware Pipeline

```
HTTP Request
  │
  ├── EncryptCookies
  ├── VerifyCsrfToken
  ├── Authenticate (session-based)
  ├── EagerLoadUserRelations (performance)
  │
  └── Department Middleware (route-group level):
      ├── restrict.sales  → RestrictToSales.php  (NSD routes)
      ├── restrict.wms    → RestrictToWms.php    (OD routes)
      └── restrict.csd    → RestrictToCsd.php    (CSD routes)
```

---

## 2. Folder & File Structure

### 2.1 Backend (`app/`)

```
app/
├── Console/                          # Artisan CLI commands
├── Events/                           # Event classes (Pusher broadcasts)
├── Exceptions/                       # Custom exception handlers
├── Exports/                          # Excel exports (Maatwebsite)
│   └── StsExport.php
├── Helper/
│   └── helper.php                    # Global functions (autoloaded via composer)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                     # Login, Register, Password Reset (Laravel UI)
│   │   ├── Commercial/              # Client engagement controllers
│   │   ├── Csd/                     # CSD module (10 controllers)
│   │   │   ├── Concerns/            # Shared traits for CSD
│   │   │   ├── CsdAmcController.php
│   │   │   ├── CsdChangeRequestController.php
│   │   │   ├── CsdClientController.php
│   │   │   ├── CsdCollectionController.php
│   │   │   ├── CsdCommunicationController.php
│   │   │   ├── CsdDashboardController.php
│   │   │   ├── CsdOpportunityController.php
│   │   │   ├── CsdRenewalController.php
│   │   │   ├── CsdSupportController.php
│   │   │   └── CsdTeamReportController.php
│   │   ├── Od/                      # Operations controllers
│   │   ├── Reports/                 # Report controllers
│   │   │   ├── AdvancedReportController.php
│   │   │   ├── EmployeeReportController.php
│   │   │   └── OperationsReportController.php
│   │   ├── Sales/                   # Sales module controllers
│   │   │   ├── HandoffWizardController.php
│   │   │   ├── SalesActivityController.php
│   │   │   ├── SalesPipelineController.php
│   │   │   ├── SalesTargetController.php
│   │   │   └── ServiceCatalogController.php
│   │   │
│   │   ├── HomeController.php            # Dashboard hub (614 lines ⚠️)
│   │   ├── ClientsController.php         # Client CRUD (957 lines ⚠️)
│   │   ├── SalesDashboardController.php  # Sales dashboard data
│   │   ├── BranchManagerDashboardController.php
│   │   ├── DashboardController.php       # Chart data
│   │   ├── ReportController.php          # STS/DSR reports (574 lines ⚠️)
│   │   ├── ProjectController.php         # Project operations
│   │   ├── TaskController.php            # Task CRUD
│   │   ├── UserController.php            # User management
│   │   ├── DailyClosingController.php    # Day-closing submissions
│   │   ├── DailyTargetController.php     # Target management
│   │   └── ... (20+ more controllers)
│   ├── Kernel.php                   # Middleware registration
│   ├── Middleware/                   # 16 middleware classes
│   │   ├── Admin.php                # Admin-only access
│   │   ├── TeamLeader.php           # TL-only access
│   │   ├── Employee.php             # Employee-only access
│   │   ├── RestrictToSales.php      # NSD department gate
│   │   ├── RestrictToWms.php        # OD department gate
│   │   ├── RestrictToCsd.php        # CSD department gate
│   │   └── EagerLoadUserRelations.php  # Performance optimization
│   └── Requests/                    # Form validation (6 classes)
│       ├── ClientStoreRequest.php
│       ├── ClientUpdate.php
│       ├── TaskRequest.php
│       ├── TaskUpdate.php
│       ├── UserStoreRequest.php
│       └── UserUpdateRequest.php
├── Mail/                            # Email templates
├── Models/                          # 43 Eloquent models
│   ├── User.php                     # Core user model (with Spatie roles)
│   ├── Clients.php                  # Client/Lead entity
│   ├── ClientHistory.php            # STS/DSR entries
│   ├── DepartmentProjects.php       # Project entity
│   ├── Task.php                     # Task entity
│   ├── CsdClientAssignment.php      # CSD client mapping
│   ├── CsdAmcContract.php           # AMC contracts
│   ├── CsdRenewal.php               # Renewal tracking
│   └── ... (35+ more models)
├── Notifications/                   # Push notification classes
├── Providers/                       # Service providers
├── Repositories/                    # Data access layer (5 repos)
│   ├── BaseRepository.php           # Generic CRUD base class
│   ├── ClientRepository.php
│   ├── ProjectRepository.php
│   ├── TaskRepository.php
│   └── ServiceCatalogRepository.php
└── Services/                        # Business logic layer
    ├── BranchManagerDashboardService.php
    ├── BranchScopeService.php
    ├── ClientServices.php
    ├── CsdTeamScopeService.php
    ├── DailyClosingService.php
    ├── ProjectNotificationService.php
    ├── UserPerformanceService.php
    ├── Commercial/                  # Engagement services
    ├── Csd/                         # CSD services (15 classes — best organized)
    │   ├── CsdAmcService.php
    │   ├── CsdChangeRequestService.php
    │   ├── CsdClientResolverService.php
    │   ├── CsdClientService.php
    │   ├── CsdCollectionService.php
    │   ├── CsdCommunicationService.php
    │   ├── CsdDashboardService.php
    │   ├── CsdHandoffService.php
    │   ├── CsdMigrationService.php
    │   ├── CsdOpportunityService.php
    │   ├── CsdReminderService.php
    │   ├── CsdRenewalService.php
    │   ├── CsdSupportService.php
    │   └── CsdTeamReportService.php
    ├── Od/                          # Operations services
    ├── Reports/                     # Report services (5 classes)
    │   ├── CsdWorkReportService.php
    │   ├── NsdWorkReportService.php
    │   ├── OdWorkReportService.php
    │   ├── ReportDateRangeService.php
    │   └── ReportScopeService.php
    └── Sales/                       # Sales services (3 classes)
        ├── PipelineService.php
        ├── ServiceCatalogService.php
        └── TargetService.php
```

### 2.2 Frontend (`resources/` + `public/`)

```
resources/
├── css/
│   ├── app.css                      # Main app overrides (14KB)
│   ├── kanban.css                   # Sales Pipeline board (7KB)
│   ├── loader.css                   # Loading spinner animation
│   ├── responsive.css               # Media queries
│   └── toastr.css                   # Toast notifications
├── js/
│   ├── app.js                       # Entry point (imports bootstrap.js)
│   └── bootstrap.js                 # Axios + Laravel Echo setup
├── sass/
│   └── app.scss                     # SASS entry (compiled by Vite)
└── views/
    ├── layouts/
    │   ├── app.blade.php            # Main layout (806 lines ⚠️)
    │   ├── authlayout.blade.php     # Auth pages layout
    │   └── partials/                # Layout partials (sidebar, header)
    ├── dashboards/                  # Role-specific dashboards (18 files)
    │   ├── admin_ui.blade.php       # Admin dashboard UI
    │   ├── admin_scripts.blade.php  # Admin dashboard JS
    │   ├── sales_ui.blade.php       # Sales Exec dashboard (944 lines ⚠️)
    │   ├── sales_scripts.blade.php  # Sales JS (32KB ⚠️)
    │   ├── tl_sales_ui.blade.php    # Sales TL dashboard
    │   ├── tl_od_ui.blade.php       # OD TL dashboard
    │   ├── employee_od_ui.blade.php # OD Employee dashboard
    │   ├── pm_ui.blade.php          # Project Manager dashboard
    │   ├── branch_manager_ui.blade.php
    │   ├── csd_ui.blade.php         # CSD dashboard
    │   └── tl_csd_ui.blade.php
    ├── components/                  # Feature-level page views
    │   ├── clients/                 # Client management pages
    │   ├── projects/                # Project pages with sub-components
    │   ├── csd/                     # CSD pages (renewals, AMC, support)
    │   ├── reports/                 # Report pages (STS, DSR, employee, etc.)
    │   ├── department/              # Department management
    │   ├── targets/                 # Target configuration
    │   ├── day-closing/             # Day-closing forms & approvals
    │   ├── users/                   # User management
    │   └── ...                      # Other feature views
    ├── home.blade.php               # Dashboard hub (routes to role-specific includes)
    └── emails/                      # Email templates

public/
├── css/
│   ├── erp-theme.css                # Design system root (21KB) — CORE
│   ├── erp-components.css           # Component library (50KB) — CORE
│   ├── sales-dashboard.css          # Sales-specific styles (17KB)
│   ├── reports.css                  # Report page styles (6KB)
│   ├── auth.css                     # Auth page styles (11KB)
│   └── toastr.css                   # Toast notifications
├── assets/                          # Vendor assets (Bootstrap 4, jQuery, icons, DataTables)
├── js/                              # Public JavaScript (if any)
└── build/                           # Vite compiled output
```

### 2.3 Configuration & Database

```
config/
├── permission.php                   # Spatie Permission config
├── horizon.php                      # Queue dashboard (optional)
├── dompdf.php                       # PDF generation settings
├── excel.php                        # Excel export settings
├── chunk-upload.php                 # File upload chunking config
└── ... (standard Laravel configs)

database/
├── migrations/                      # 52 migration files
├── seeders/                         # Database seeders
└── factories/                       # Model factories

routes/
├── web.php                          # Main routes (358 lines)
├── project.php                      # Project-specific routes
├── api.php                          # API routes (minimal)
└── channels.php                     # Pusher broadcast channels
```

---

## 3. Tech Stack

### 3.1 Backend

| Technology | Version | Purpose |
|-----------|---------|---------|
| **PHP** | 8.2+ | Server-side language |
| **Laravel** | 12.x | MVC framework |
| **MySQL** | 8.x | Relational database |
| **Spatie Permission** | 6.x | Role-Based Access Control (RBAC) |
| **Yajra DataTables** | 12.x | Server-side table rendering |
| **DomPDF** (Barryvdh) | 3.x | PDF report generation |
| **Maatwebsite Excel** | 3.x | Excel/CSV export |
| **Intervention Image** | 1.x | Image processing & resizing |
| **SimpleSoftwareIO QR** | 4.x | QR code generation |
| **Pion Chunk Upload** | — | Large file chunked uploads |
| **Laravolt Avatar** | — | Auto-generated user avatars |
| **Pusher PHP Server** | 7.x | WebSocket push notifications |
| **Laravel Sanctum** | 4.x | API token auth (available but minimal use) |

### 3.2 Frontend

| Technology | Version | Purpose |
|-----------|---------|---------|
| **Blade** | (Laravel) | Server-side templating engine |
| **jQuery** | 3.x | DOM manipulation & AJAX (loaded from vendor assets) |
| **Bootstrap 4** | 4.x | CSS framework (**NOTE:** package.json has BS5, but app uses BS4 classes) |
| **Vite** | 5.x | Build tool & HMR |
| **Laravel Echo** | 1.x | Real-time event listener (Pusher) |
| **Pusher JS** | 8.x | WebSocket client |
| **Select2** | — | Searchable dropdowns |
| **DataTables** | — | Client-side table plugin (paired with Yajra backend) |
| **AlertifyJS** | — | Alert/confirmation dialogs |
| **Magnific Popup** | — | Image lightbox |
| **Slick Slider** | — | Carousel/slider |
| **FullCalendar** | — | Calendar views (Sales & Operations) |
| **ApexCharts** | — | Dashboard charts |

### 3.3 Icon Libraries

| Library | Prefix | Primary Usage |
|---------|--------|--------------|
| **Boxicons** | `bx bx-*` / `bx bxs-*` | Dashboard KPIs, metrics, navigation |
| **Material Design Icons** | `mdi mdi-*` | Buttons, action icons, sidebar |
| **Dripicons** | `dripicons-*` | Admin KPI cards |

### 3.4 Infrastructure

| Component | Technology |
|-----------|-----------|
| **Build Tool** | Vite 5.x with `laravel-vite-plugin` |
| **Real-time** | Pusher (WebSockets) |
| **Mail** | Laravel Mail (SMTP) |
| **Storage** | Local filesystem with `storage:link` |
| **Queue** | Database driver (Laravel Horizon available) |
| **Cache** | File-based (default Laravel) |

---

## 4. Design Patterns In Use

| Pattern | Where Used | Status |
|---------|-----------|--------|
| **Service Layer** | `app/Services/` | ✅ Active — CSD module best example |
| **Repository Pattern** | `app/Repositories/` | ⚠️ Partial — only 4 of 43 models have repos |
| **FormRequest Validation** | `app/Http/Requests/` | ⚠️ Partial — only 6 request classes exist |
| **Middleware Authorization** | `app/Http/Middleware/` | ✅ Active — department-level and role-level |
| **Blade Includes/Partials** | `resources/views/` | ✅ Active — dashboards use include pattern |
| **Observer/Events** | `app/Events/` | ⚠️ Minimal usage |
| **Helper Functions** | `app/Helper/helper.php` | ⚠️ Global functions (prefer services) |

### Target Architecture (Where We're Heading)

```
Controller (thin) → FormRequest (validation) → Service (logic) → Repository (data) → Model (ORM)
                                                                                          │
                                                                                     Database
```

All new code should follow: **Controller calls Service, Service calls Repository, Repository calls Model.**
