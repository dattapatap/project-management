# 🎨 Design System Document

## Project: WMS — Workforce Management System / ERP

---

## 1. Color & Theme

### 1.1 Department Colors (Module Identity)

Each department has a unique color identity applied automatically via the `<body>` class.

| Department | Primary Color | HEX | Gradient | Body CSS Class |
|-----------|--------------|-----|----------|---------------|
| **NSD (Sales)** | Purple | `#7F00FF` | `linear-gradient(135deg, #7F00FF, #E100FF)` | `nsd-module` |
| **OD (Operations)** | Blue | `#2E86DE` | `linear-gradient(135deg, #2E86DE, #54A0FF)` | `od-module` |
| **CSD (Customer Service)** | Teal | `#10AC84` | `linear-gradient(135deg, #10AC84, #1DD1A1)` | `csd-module` |
| **Admin** | Dark Blue | `#2C3E50` | `linear-gradient(135deg, #2C3E50, #3498DB)` | — |

### 1.2 CSS Custom Properties (`:root` Variables)

These are defined in `erp-theme.css` and used globally:

```css
:root {
    /* ── Brand Colors ── */
    --erp-brand:          #48A596;    /* Primary accent (teal) */
    --erp-brand-dark:     #2a6b62;    /* Darker brand */
    --erp-brand-dark-glow:#1e5c54;    /* Sidebar hover */

    /* ── Department Accents ── */
    --dept-admin:    #5b6abf;    /* Admin dashboard accent */
    --dept-nsd:      #556ee6;    /* Sales accent */
    --dept-od:       #e67e22;    /* Operations accent */
    --dept-csd:      #48A596;    /* Customer Service accent */
    --dept-reports:  #6f42c1;    /* Reports accent */

    /* ── Neutral Palette ── */
    --erp-bg:        #f4f6f9;    /* Page background */
    --erp-card-bg:   #ffffff;    /* Card background */
    --erp-border:    #e8ecf1;    /* Border/divider color */
    --erp-text:      #2a3042;    /* Primary text */
    --erp-text-muted:#6c757d;    /* Secondary text */

    /* ── Spacing & Radius ── */
    --erp-radius:    8px;        /* Default border radius */
    --erp-radius-lg: 12px;       /* Large radius (cards, buttons) */
}
```

### 1.3 Auth Page Theme

The login/register pages use a separate color system defined in `auth.css`:

```css
:root {
    --auth-primary:       #42968B;    /* Auth brand color (teal) */
    --auth-primary-dark:  #2e6d64;    /* Hover state */
    --auth-primary-light: #5cb8ae;    /* Active state */
}
```

### 1.4 Gradient Presets (CSS Classes)

Available as utility classes in `erp-theme.css`:

| CSS Class | Gradient | Use For |
|-----------|----------|---------|
| `.gradient-primary` | `#667eea → #764ba2` | Primary KPI cards |
| `.gradient-info` | `#17ead9 → #6078ea` | Info/department cards |
| `.gradient-success` | `#11998e → #38ef7d` | Success/matured metrics |
| `.gradient-warning` | `#f7971e → #ffd200` | Warning/pending metrics |
| `.gradient-danger` | `#fc5c7d → #6a82fb` | Danger/overdue metrics |

Sales-specific gradients (`sales-dashboard.css`):

| CSS Class | Gradient | Use For |
|-----------|----------|---------|
| `.gradient-green-teal` | `#11998e → #38ef7d` | Matured sales card |
| `.gradient-purple-blue` | `#7F00FF → #667eea` | Callbacks card |
| `.gradient-orange-red` | `#f7971e → #fc5c7d` | Unassigned leads card |
| `.gradient-sky-blue` | `#36d1dc → #5b86e5` | Active members card |

### 1.5 Semantic Color Classes

| CSS Class | Color | Usage |
|-----------|-------|-------|
| `.text-premium-dark` | `#1a1a2e` | Headings, important text |
| `.text-premium-muted` | `#6c757d` | Descriptions, secondary info |
| `.text-success` | Bootstrap green | Positive metrics, growth |
| `.text-danger` | Bootstrap red | Overdue, alerts |
| `.text-warning` | Bootstrap yellow | Pending, caution |
| `.text-info` | Bootstrap blue | Active, information |

---

## 2. Fonts & Typography

### 2.1 Font Family

**Primary Font:** [Inter](https://fonts.google.com/specimen/Inter) (Google Fonts)

```css
/* Loaded in erp-theme.css */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
```

**Font Weights Used:**
| Weight | Name | Usage |
|--------|------|-------|
| 400 | Regular | Body text, descriptions |
| 500 | Medium | Sub-headings, labels |
| 600 | Semi-bold | Card titles, nav items |
| 700 | Bold | Headings, KPI values, CTAs |

### 2.2 Base Font Size

```css
html {
    font-size: 87.5%;  /* = 14px base (compact ERP scale) */
}
```

All `rem` values are relative to this 14px base.

### 2.3 Font Size Scale (CSS Classes)

| CSS Class | Size (rem) | Approx. Pixels | Usage |
|-----------|-----------|----------------|-------|
| `.font-size-10` | 0.733rem | ~10px | Tiny labels, badges |
| `.font-size-11` | 0.8rem | ~11px | Sub-labels |
| `.font-size-12` | 0.867rem | ~12px | Table cells, captions |
| `.font-size-13` | 0.867rem | ~12px | Descriptions, meta text |
| `.font-size-14` | 0.933rem | ~13px | Default body text |
| `.font-size-15` | 1rem | 14px | Standard text |
| `.font-size-16` | 1.067rem | ~15px | Card titles |
| `.font-size-18` | 1.2rem | ~17px | Section headings |
| `.font-size-20` | 1.25rem | ~18px | Page titles |
| `.font-size-22` | 1.35rem | ~19px | Large headings |
| `.font-size-24` | 1.5rem | ~21px | Hero headings |

### 2.4 Heading Scale

```css
h1, .h1 { font-size: 1.65rem; }   /* ~23px */
h2, .h2 { font-size: 1.4rem; }    /* ~20px */
h3, .h3 { font-size: 1.2rem; }    /* ~17px */
h4, .h4 { font-size: 1.05rem; }   /* ~15px */
h5, .h5 { font-size: 0.95rem; }   /* ~13px */
h6, .h6 { font-size: 0.85rem; }   /* ~12px */
```

---

## 3. UI Components (CSS Classes Reference)

### 3.1 Cards

| CSS Class | Description | Usage |
|-----------|-------------|-------|
| `.card.sales-card-glass` | Glassmorphism card with subtle blur | Dashboard sections |
| `.card.admin-kpi-card` | Admin KPI metric card | Admin dashboard |
| `.card.sales-metric-card-green` | Green-themed metric card | Matured sales |
| `.card.sales-metric-card-purple` | Purple-themed metric card | Callbacks |
| `.card.sales-metric-card-red` | Red-themed metric card | Alerts/overdue |
| `.card.sales-metric-card-blue` | Blue-themed metric card | Team/active |
| `.card.erp-card` | Standard ERP card | General use |
| `.erp-hero` | Dark hero banner | List page headers |

**Card Usage Example:**
```html
<div class="card sales-card-glass">
    <div class="card-body">
        <h4 class="card-title text-premium-dark font-size-16">Section Title</h4>
        <p class="text-muted font-size-12">Description text</p>
    </div>
</div>
```

### 3.2 Badges

| CSS Class | Color | Usage |
|-----------|-------|-------|
| `.badge-premium.badge-premium-success` | Green | Active, completed |
| `.badge-premium.badge-premium-warning` | Yellow | Pending, today's |
| `.badge-premium.badge-premium-danger` | Red | Overdue, alert |
| `.badge-premium.badge-premium-info` | Blue | Active count |
| `.badge-soft-*` | Soft background variants | Bootstrap-based |

### 3.3 Buttons

| CSS Class | Style | Usage |
|-----------|-------|-------|
| `.btn-gradient-purple` | Purple gradient CTA | Sales primary actions |
| `.btn-soft-danger` | Soft red | Nudge, alert actions |
| `.btn-soft-success` | Soft green | Confirm actions |
| `.btn-outline-*` | Bootstrap outlines | Standard actions |
| `.btn.rounded-pill` | Pill-shaped button | Compact actions |

### 3.4 KPI Metric Icons

```html
<div class="metric-badge-icon gradient-green-teal">
    <i class="bx bx-check-double"></i>
</div>
```

### 3.5 Tables

| CSS Class | Purpose |
|-----------|---------|
| `.table-centered` | Vertically centered cells |
| `.table-nowrap` | No text wrapping |
| `.custom-scroll` | Custom scrollbar styling |
| `.oversight-row` | Hoverable row with subtle highlight |

### 3.6 Page Headers

```html
<div class="erp-page-header">
    <div class="erp-page-header__main">
        <h4 class="erp-page-title">Page Title</h4>
        <p class="erp-page-subtitle">Description text</p>
    </div>
    <div class="erp-page-header__actions">
        <a href="#" class="btn btn-sm btn-outline-primary">Action</a>
    </div>
</div>
```

### 3.7 Dashboard Tabs (Team Leaders)

```html
<ul class="nav nav-tabs dashboard-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="...">
            <i class="mdi mdi-shield-crown"></i> Team Oversight
        </a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active tab-content-animate">
        <!-- content -->
    </div>
</div>
```

---

## 4. Layout & Spacing

### 4.1 Page Structure

```
┌──────────────────────────────────────────────┐
│ #page-topbar (header with logo + user menu)  │
├──────────────┬───────────────────────────────┤
│              │                               │
│  .vertical-  │  .main-content               │
│   menu       │   └── .page-content          │
│  (sidebar)   │       └── .container-fluid   │
│              │           .erp-page           │
│              │           └── @yield('content')│
│              │                               │
├──────────────┴───────────────────────────────┤
│ footer                                        │
└──────────────────────────────────────────────┘
```

### 4.2 Grid Patterns

| Pattern | Bootstrap Classes | Usage |
|---------|------------------|-------|
| 4-column KPI row | `col-xl-3 col-md-6` | KPI cards |
| Content + sidebar | `col-xl-8` + `col-xl-4` | Dashboard layouts |
| Full-width section | `col-12` | Tables, charts |
| 3-column grid | `col-xl-4 col-md-6` | Card grids |

### 4.3 Spacing Scale

| Bootstrap Class | Value | Usage |
|----------------|-------|-------|
| `mb-1` | 4px | Tight spacing |
| `mb-2` / `py-2` | 8px | Default element spacing |
| `mb-3` / `py-3` | 16px | Card body padding |
| `mb-4` | 24px | Section spacing |
| `pb-5` | 48px | Page bottom padding |

---

## 5. Icon Libraries

### 5.1 Available Icons

| Library | Prefix | CDN/Path | Primary Usage |
|---------|--------|----------|---------------|
| **Boxicons** | `bx bx-*` (line) / `bx bxs-*` (solid) | `assets/css/icons.min.css` | Dashboard metrics, navigation |
| **Material Design Icons** | `mdi mdi-*` | `assets/css/icons.min.css` | Buttons, sidebar, forms |
| **Dripicons** | `dripicons-*` | `assets/css/icons.min.css` | Admin KPI cards |

### 5.2 Icon Size

Icons follow the same font-size classes: `.font-size-14`, `.font-size-16`, `.font-size-18`, etc.
For large KPI icons: use `.icon-lg` (define if not exists) or inline size on the icon container.

---

## 6. Animation & Interaction

| CSS Class / Effect | Description | Where Used |
|-------------------|-------------|-----------|
| `.animate-pulse` | Pulsing attention animation | Overdue badges |
| `.tab-content-animate` | Smooth tab transition | Dashboard tab switching |
| `.oversight-row:hover` | Row highlight on hover | Team oversight tables |
| `.sales-metric-card-link:hover` | Card lift/scale effect | Clickable KPI cards |
| `.erp-kpi-clickable` | Cursor pointer + hover effect | Admin KPI cards |
| `.fade-in` | Entry fade animation | Dynamic content |

---

## 7. CSS File Ownership — Where to Put New Styles

| I'm building... | Put CSS in... |
|-----------------|--------------|
| A global utility class | `public/css/erp-theme.css` |
| A reusable component | `public/css/erp-components.css` |
| Sales-specific UI | `public/css/sales-dashboard.css` |
| Report-specific UI | `public/css/reports.css` |
| Auth page styling | `public/css/auth.css` |
| CSD-specific UI | Create `public/css/csd-dashboard.css` (and include in layout) |
| OD-specific UI | Create `public/css/od-dashboard.css` (and include in layout) |

---

## 8. Design Rules (For AI & Developers)

1. **NEVER use inline `style=""` attributes** — always create or use CSS classes
2. **Match department colors** — Sales=Purple, OD=Blue, CSD=Teal
3. **Use Inter font only** — don't introduce new fonts
4. **Use existing CSS variables** (`var(--erp-*)`) for consistency
5. **Cards must use glassmorphism** — `.sales-card-glass` or equivalent
6. **Metrics use icon badges** — `.metric-badge-icon` with gradient
7. **Tables are responsive** — wrap in `.table-responsive.custom-scroll`
8. **Badges use premium style** — `.badge-premium.badge-premium-{variant}`
9. **Use Bootstrap 4 classes** — NOT Bootstrap 5 (e.g., `mr-2` not `me-2`)
10. **Page headers use `.erp-page-header`** — standard header pattern for all pages
