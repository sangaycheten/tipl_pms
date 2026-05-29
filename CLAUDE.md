# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TashiCell Online PMS — a Laravel 12 web application for managing employee performance reviews (PMS) at TashiCell (Bhutan Telecom company). Built on PHP 8.2+.

## Common Commands

```bash
# Install dependencies (after fresh clone, remove vendor/ first)
composer install

# Clear and rebuild config cache (required after .env changes)
php artisan config:clear && php artisan cache:clear && php artisan config:cache

# Clear compiled views
php artisan view:clear

# Run database migrations
php artisan migrate

# Run queue worker (requires supervisor in production)
php artisan queue:listen --tries=10

# Run tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/Feature/ExampleTest.php

# Compile assets (legacy Laravel Elixir + Gulp)
npm run dev        # watch mode
npm run prod       # production build
```

## Architecture

### Directory Structure
- `app/Http/Controllers/Application/` — all user-facing controllers (PMS submission, goals, employees, departments, etc.)
- `app/Http/Controllers/Automation/` — goal-setting automation: `GoalController`, `GoalAppraisalController`, `CommonGoalController`
- `app/Http/Controllers/Reports/` — report generation
- `app/Http/Controllers/SystemAdmin/` — HR admin / system configuration
- `app/Http/Controllers/sso/` — SSO login integration
- `app/` (root) — Eloquent models live directly here (no `Models/` subdirectory)
- `app/Helpers/functions.php` — globally autoloaded utility functions (`UUID()`, date helpers, etc.)
- `app/Helpers/constants.php` — UUID-based constants for PMS statuses, employee positions, PMS window dates
- `packages/laravelcollective/html/` — local fork of `laravelcollective/html` (path repository in `composer.json`)
- `resources/views/` — Blade templates organized by feature: `application/`, `automation/`, `goals/`, `reports/`, etc.
- `routes/web.php` — all routes (migrated from Laravel 5.2's `app/Http/routes.php`)

### Database Conventions
- **Table prefixes**: `mas_` for master/lookup tables, `pms_` for PMS submission tables, `sys_` for system configuration
- **Primary keys**: UUIDs generated via MySQL's `UUID()` function (call the global `UUID()` helper). Models use `$incrementing = false` and typically `public $timestamps = false`
- **User model**: Maps to `mas_employee` table. Auth field is `EmpId` (not `email`)

### Key Domain Concepts

**PMS Cycle**: Two rounds per year. Window dates are defined by constants in `constants.php`:
- First PMS: May (`CONST_PMSSETTING_FIRSTPMSSTARTDATE` / `CONST_PMSSETTING_FIRSTPMSENDDATE`)
- Second PMS: July (`CONST_PMSSETTING_SECONDPMSSTARTDATE` / `CONST_PMSSETTING_SECONDPMSENDDATE`)

**PMS Submission Workflow**: Draft → Submitted → Verified/Sent Back → Approved. Statuses are UUID constants (`CONST_PMSSTATUS_*`).

**Employee Hierarchy**: Stored in `mas_hierarchy`. Level 1 is direct line manager (appraiser); Level 2 is above. The `mas_hierarchy` table maps employees to their reporting levels.

**Goals Architecture**: `pms_goals_target` (goal header) → `pms_goals_targetdetails` (per-department assignment) → `pms_task_targetdetails` (per-employee tasks). Common Goals (`CommonGoal` / `CommonGoalAssignment`) can be pushed to entire departments.

### Authentication
- **Web sessions**: Standard Laravel session auth using `mas_employee` table
- **API**: HTTP Basic Auth checked against `api_users` table via `AuthenticateAPI` middleware (alias: `apiauth`)
- **Admin check**: `isadmin` middleware requires `RoleId = 1` on the authenticated user

### Base Controller (`Controller.php`)
Provides shared methods used across all controllers:
- `sendMail()` / `sendMailAlternate()` — email dispatch; in `local` env all mail goes to the dev address
- `sendSMS()` / `sendSMSTashiCell()` — SMS via internal gateway at `10.76.177.100`; in `local` env SMS is a no-op
- `saveError()` — logs exceptions to `ErrorLog` model and notifies via email + SMS
- `saveAuditTrail()` — writes a JSON snapshot of a record to `sys_databasechangehistory`
- Many shared DB query helpers (`fetchActiveDepartments()`, `getDepartmentEmployees()`, etc.)

### Email / SMS Behaviour
In `local` env, all outbound email is redirected to the developer address (`sw_engineer4.sdu@tashicell.com`). SMS calls return early (`return true`) without hitting the gateway. This is controlled by `config('app.env')` checks inside the base controller.

### Assets
Assets use the legacy **Laravel Elixir** pipeline (Gulp 3 + Bootstrap Sass). There is no Vite/Mix. The compiled output goes to `public/`.

### Notifications / Queue
Background jobs use the database queue driver. In production, supervisor runs `php artisan queue:listen --tries=10` to process them.

### Production URL
`https://pms.tashicell.com` — `AppServiceProvider` forces HTTPS and the root URL in production.
