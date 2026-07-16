# Allocore Suite

A multi-module SaaS platform built on Laravel that provides central authentication, team-based access control, per-module billing, and a unified analytics shell.

## What is Allocore Suite?

Allocore Suite is the central hub for the Allocore product family. It lets customers manage teams, choose subscription plans, and access connected tools from one dashboard. Each tool is delivered as a Laravel module and can be gated through plans.

## Modules

| Module | Purpose |
|--------|---------|
| **AuditPro** | Qualitative business maturity assessments across 5 pillars and 25 criteria. |
| **FinancialPlatform** | Financial analyses (GmbH, Jahresabschluss, Immobilien) and KPI tracking. |
| **LeadQuality** | Contact and pipeline management for sales and marketing. |
| **InvoiceMaker** | Clients, invoices, and payment tracking. |

## Deep KPIs

The FinancialPlatform includes a **Deep KPI** dashboard that turns the KPI definitions from the Deep KPI workbook into concrete, measurable metrics:

- **Umsatzbedarf** — target vs. actual sales, with configurable sources:
  - InvoiceMaker revenue
  - Manual input
  - SeoStory manual revenue fallback
  - (Extensible for `financial.seostory.de` API integration)
- **Leadqualität** — impressions, clicks, CTR, average Google position, and page value with current vs. previous month comparison.
- **Abschlussquote** — conversion rate from leads to new customers, comparing current and previous month using LeadQuality contacts and InvoiceMaker clients.
- **Vertragstreue Kunden** — average days from invoice date to payment, computed from InvoiceMaker payments.

KPI thresholds are managed in **Admin → KPI Thresholds** and drive the traffic-light status indicators.

## Admin Panel

The advanced admin panel (`/admin`) gives platform administrators full control over the tenant layer:

- **Dashboard** — users, teams, active modules, plans, subscriptions, analyses, and audits.
- **User Management** — search, view profiles, change roles, delete accounts.
- **Team Management** — list teams, edit profiles, view members and subscriptions, remove members, delete teams.
- **Catalog** — manage modules (enable/disable) and plans with pricing, billing scope, and module mapping.
- **Billing** — approve/reject pending bank transfers and cancel active/pending subscriptions.
- **Tool Data**
  - **AuditPro** — view audits and templates across all teams.
  - **Financial** — view analyses across all teams.
  - **KPI Thresholds** — edit green/yellow thresholds, weights, and active flags.

Admin access is controlled by the `admin` role managed through Spatie Laravel Permission.

## Installation

```bash
git clone https://github.com/mrshahbazdev/allocore-suite.git
cd allocore-suite
cp .env.example .env
composer install
npm install
npm run build
php artisan key:generate
php artisan migrate --seed
```

The seeder creates an admin user. Check `database/seeders/CoreSeeder.php` for the default credentials.

## Environment

Key variables you may need to configure:

- `APP_NAME` — application name shown in the UI.
- `APP_URL` — public URL of the installation.
- `DB_*` — database connection.
- `STRIPE_*` / `PAYPAL_*` — payment gateway credentials for subscriptions.
- `MODULE_*` API keys for third-party tool integrations.

## Testing

```bash
composer exec pint -- --test
php artisan test
```

## Architecture

- Laravel modules live under `Modules/`. Each module has its own `app/`, `database/`, `resources/`, `routes/`, and `tests/` directories.
- Shared UI uses `resources/views/layouts/shell.blade.php` with a sidebar for tools and admin sections.
- Admin controllers are under `app/Http/Controllers/Admin` and use `auth` + `admin` middleware.
- Cross-team admin queries bypass the module-level `BelongsToCurrentTeam` global scope using `withoutGlobalScope('current_team')`.

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
