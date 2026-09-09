# 📋 Product Requirements Document (PRD)

## Project: WMS — Workforce Management System / ERP

**Product Owner:** Digitalnock IT Solutions  
**Application Type:** Internal ERP (Enterprise Resource Planning)  
**Version:** Production (Active Development)

---

## 1. What to Build

WMS is a comprehensive **internal ERP system** that manages end-to-end business operations for Digitalnock IT Solutions — an IT services company. The system covers the complete client lifecycle from **lead acquisition** (Sales) → **project delivery** (Operations) → **post-sale servicing** (Customer Service).

### Core Objective
A single unified platform where all departments (Sales, Operations, Customer Service) can track their work, collaborate on client data, and report performance — replacing spreadsheets, manual tracking, and fragmented tools.

### What the Application Does
1. **Captures Leads** — Sales team adds/imports client leads and tracks them through stages (Fresh → Followup → Meeting → Matured)
2. **Manages Projects** — When a lead converts ("Matures"), projects are created and assigned to the Operations team for delivery
3. **Services Clients** — Matured clients are handed to the Customer Service team for renewals, AMC contracts, support tickets, and upselling
4. **Tracks Performance** — Daily targets, day-closing reports, leaderboards, and advanced reports give management visibility
5. **Controls Access** — Role-based permissions ensure each user only sees and does what they're authorized to

---

## 2. Targeted Users

### Primary Users (Daily Active)

| User Role | Department | What They Do | Count |
|-----------|-----------|--------------|-------|
| **Sales Executive** | NSD (Sales) | Add leads, update STS/DSR, schedule callbacks, close deals | Multiple per branch |
| **CSD Executive** | CSD (Customer Service) | Manage renewals, support tickets, AMC contracts, collections | Multiple per branch |
| **Developer / Designer / SEO-Developer** | OD (Operations) | Work on assigned tasks, log time, submit day-closing | Multiple per branch |
| **Accountant** | OD (Operations) | Track payments, generate invoices | 1-2 per branch |

### Management Users (Supervisory)

| User Role | Department | What They Do |
|-----------|-----------|--------------|
| **Team Leader** | Per Department | Oversee team members, allocate leads, approve work, view team reports |
| **Project Manager** | Cross-Department | Manage project lifecycle, assign tasks, track deliverables |
| **Branch Manager** | All Departments | Branch-level oversight across all departments, approve day-closings |

### Administrative Users

| User Role | Scope | What They Do |
|-----------|-------|--------------|
| **Admin (Super Admin)** | Entire System | Full access — manage users, departments, teams, targets, all reports |

### User Hierarchy
```
Admin
├── Branch Manager (branch-level oversight)
│   ├── Team Leader (team-level oversight, per department)
│   │   ├── Sales Executive (NSD)
│   │   ├── Developer / Designer / SEO-Developer / Accountant (OD)
│   │   └── CSD Executive (CSD)
│   └── Project Manager (project-level oversight)
```

---

## 3. Features

### 3.1 Sales / NSD Module (Department 1)

| Feature | Description | Who Uses It |
|---------|-------------|-------------|
| **Client Management** | Full CRUD for leads/clients with status lifecycle (Fresh → Followup → Matured → Not Interested) | Sales Exec, TL, Admin |
| **STS (Sales Tracking Sheet)** | Daily interaction log per client — notes, status updates, callback scheduling | Sales Exec |
| **DSR (Daily Sales Report)** | Client-level daily report with status progression tracking | Sales Exec, TL |
| **TBRO Callbacks** | Scheduled follow-up dates ("To Be Reached On") — reminders appear on dashboard | Sales Exec |
| **Lead Allocation** | Admin/TL assigns fresh leads to Sales Executives | TL, Admin |
| **Bulk Client Upload** | CSV import for mass client creation | Sales Exec, TL, Admin |
| **Sales Pipeline Kanban** | Visual drag-drop board showing leads across pipeline stages | Sales Exec, TL |
| **Sales Activity Calendar** | FullCalendar-based view of callbacks, meetings, and scheduled activities | Sales Exec, TL |
| **Sales-to-OD Handoff** | Automated wizard to transition a matured client from Sales to Operations | TL, Admin |
| **Nudge System** | TL can send nudge notifications to executives with overdue follow-ups | TL |
| **Leaderboard** | Monthly/yearly sales rankings with target achievement metrics | All Sales |
| **Sales Targets** | Admin sets monthly sales goals per user/team | Admin |

### 3.2 Operations / OD Module (Department 2)

| Feature | Description | Who Uses It |
|---------|-------------|-------------|
| **Project Management** | Full lifecycle tracking (Active → Hold → Completed) with categories | PM, TL, Admin |
| **Task Management** | Create, assign, track tasks within projects with deadlines | PM, TL, OD Employees |
| **Task Timer / Attendance** | Global check-in/check-out timer with time logging per task | OD Employees |
| **Task Logs** | Time-tracked work entries per task (hours, description) | OD Employees |
| **Operations Calendar** | Calendar view of task deadlines, milestones, and schedules | PM, TL |
| **Team-Project Assignment** | Projects assigned to teams with category-based routing | PM, TL |

### 3.3 Customer Service / CSD Module (Department 3)

| Feature | Description | Who Uses It |
|---------|-------------|-------------|
| **Client Assignments** | Post-sale client handoff and ongoing account management | CSD Exec, TL |
| **AMC Contracts** | Annual Maintenance Contract creation, tracking, and document management | CSD Exec, TL |
| **Renewals** | Domain, hosting, and service renewal tracking with due-date alerts | CSD Exec, TL |
| **Support Tickets** | Client support request lifecycle management | CSD Exec, TL |
| **Communications Log** | Record of all client interactions (calls, emails, meetings) | CSD Exec |
| **Change Requests** | Client change request tracking with OD transfer capability | CSD Exec, TL |
| **Collections** | Payment collection follow-ups and tracking | CSD Exec, TL |
| **Opportunities** | Upsell/cross-sell opportunity logging and tracking | CSD Exec, TL |

### 3.4 Cross-Department Features

| Feature | Description | Who Uses It |
|---------|-------------|-------------|
| **Dashboards** | Role-specific dashboards with KPI cards, charts, and actionable widgets | All Users |
| **Day Closing Reports** | End-of-day summary submission (mandatory after 6 PM) | All Non-Admin |
| **Daily Targets** | Admin-set daily goals per user with tracking | Admin → All |
| **User Management** | Employee CRUD with role, department, and branch assignment | Admin |
| **Department & Team Management** | Organizational structure configuration | Admin |
| **Multi-Branch System** | Branch-scoped data isolation for multi-office setup | Admin, Branch Manager |
| **Notifications** | Real-time push notifications via Pusher/WebSockets | All Users |
| **Profile Management** | User profile editing, avatar, social links, password change | All Users |
| **Document Management** | Chunked file upload for client and project documents | Various |
| **Reports Engine** | Advanced reports — Employee Performance, Operations, Projects, Sales, CSD Team | Management |
| **Client Domains** | Domain registration tracking per client | Sales, CSD |
| **Client Payments** | Payment tracking per project/client with pending amounts | PM, Accountant, Admin |
| **Client Engagement** | Commercial engagement event logging | Admin, TL |

### 3.5 Business Rules

| Rule | Description |
|------|-------------|
| **Client Status Flow** | `Fresh → Followup → Meeting Fixed → Hot/Warm Perspective → Matured` (or `Not Interested` at any stage) |
| **Day Closing Mandatory** | Banner appears after 6 PM if not submitted; required for all non-admin roles |
| **Lead Assignment** | Only Admin and Team Leaders can assign leads |
| **Matured Handoff** | When client becomes "Matured", eligible for project creation and CSD assignment |
| **Branch Scoping** | Branch Managers only see their branch data |
| **Team Scoping** | Team Leaders only see their team members' data |
