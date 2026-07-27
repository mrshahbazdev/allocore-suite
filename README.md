# Allocore Suite

A modular multi-tenant SaaS platform built on Laravel. It provides central authentication, team-based access control, per-module billing, and a unified workspace for business tools.

## What is Allocore Suite?

Allocore Suite is the central hub for the Allocore product family. Customers can create teams, subscribe to plans, and access connected tools from a single dashboard. Each tool is delivered as a Laravel module and can be gated through plans.

## Modules

| Module | Purpose |
|--------|---------|
| **AuditPro** | Qualitative business maturity assessments across 5 pillars and 25 criteria. |
| **ClusterForge** | AI keyword & topic cluster generator. |
| **FinancialPlatform** | Deep financial KPIs, revenue development, GSC/SeoStory sync, bank import, budgets and exchange rates. |
| **InvoiceMaker** | Clients, invoices, estimates, expenses, products and payments. |
| **LeadOS** | B2B lead generation, AI scoring and CRM pipeline. |
| **TimeButler** | Employee vacation, absence and time tracking with a team calendar. |
| **PlanHive** | Multi-tenant project management with tasks, goals, calendar, contacts and documents. |
| **KpiTool** | Bilingual KPI catalog, monthly spreadsheet, targets, charts and CSV export. |
| **LoopEngine** | Decision-loop SOP builder with steps, runs, audit trails and webhooks. |
| **SmartKpi** | Hierarchical KPI management with companies, departments, problems, actions, forecasts and goals. |
| **CashCore** | Profit First financial intelligence: cash transparency, leak detection and profit allocation. |
| **BunnyBand** | Reward-based micro-task platform with tasks, referrals, levels, wallet, deposits and withdrawals. |
| **DentalTrack** | QR-based production tracking for dental labs with orders, workstations and AI predictions. |
| **FocusMatrix** | Bilingual productivity OS for managers using the Only-You-Principle. |
| **OrgMatrix** | Organizational intelligence: visualize org structures, roles, people and succession plans. |
| **VisionFlow** | Values-to-mission operating system: values, principles, strategic goals, vision and missions. |
| **Nur-Du** | Vision alignment: vision statement, principles, priorities, decisions and vision checks. |
| **SweetSpot** | Customer sweet-spot scoring to identify profitable, low-effort and high-growth customers. |

## Installation

### Requirements

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- SQLite (default) or MySQL/PostgreSQL
- A queue worker and cron for scheduled commands

### Quick install (CLI)

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

### Web installer

If you prefer a browser-based setup:

1. Copy `.env.example` to `.env`.
2. Make sure `.env` is writable by the web server (`chmod 664 .env`).
3. Set `APP_INSTALLED=false` in `.env`.
4. Open `http://your-domain.test/install` in a browser.
5. Follow the wizard to create the database, admin account and seed core data.

### Default accounts

| Email | Password | Role |
|-------|----------|------|
| `admin@allocore.test` | `password` | Admin |
| `demo@allocore.test` | `password` | Demo user (created by DemoSeeder) |

Run the demo seeder manually to create a sample team, subscription and records:

```bash
php artisan db:seed --class=DemoSeeder
```

## Configuration

Key environment variables you may need to configure in `.env`:

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | Application name shown in the UI. |
| `APP_URL` | Public URL of the installation. |
| `DB_*` | Database connection (SQLite by default). |
| `STRIPE_*` / `PAYPAL_*` | Payment gateway credentials for subscriptions. |
| `OPENAI_API_KEY` | Enables AI assistant responses (fallback keyword logic works without it). |
| `SERVICES_SSL_COMMAND` | Optional SSL command for custom domains, e.g. `certbot certonly -d {domain} ...`. |
| `MODULE_*` | API keys for third-party tool integrations. |

## Queue, scheduler and SSL renewal

Run a queue worker for notifications, scheduled reports and imports:

```bash
php artisan queue:work
```

Add the Laravel scheduler to cron (runs every minute):

```cron
* * * * * cd /path/to/allocore-suite && php artisan schedule:run >> /dev/null 2>&1
```

Renew or request SSL certificates for custom domains manually:

```bash
php artisan ssl:renew          # all teams with custom domains
php artisan ssl:renew {teamId} # a specific team
```

## Platform features

- **Team workspace** with multi-tenant data scoping.
- **Subscription billing** with Stripe, PayPal and bank transfer support.
- **Per-team module permissions** and role-based access.
- **Scheduled reports** with PDF/CSV generation and email delivery.
- **Bulk import wizard** for CSV and Excel uploads with column mapping.
- **Real-time notifications** via Server-Sent Events and browser push toasts.
- **AI advisor** and module-aware AI assistant.
- **Cross-tool workspace** with insights and recommendations.
- **White-label / custom domains** with DNS verification and SSL automation hooks.
- **API keys** and public API documentation (`/api-docs`).
- **Admin panel** for users, teams, plans, billing, exports and system settings.

## Testing

```bash
composer exec pint -- --test
php artisan test
```

## Architecture

- Laravel modules live under `Modules/`. Each module has its own `app/`, `database/`, `resources/`, `routes/` and `tests/`.
- Shared UI uses `resources/views/layouts/shell.blade.php` with a sidebar for tools and admin sections.
- Admin controllers are under `app/Http/Controllers/Admin` and use `auth` + `admin` middleware.
- Cross-team admin queries bypass the module-level `BelongsToCurrentTeam` global scope using `withoutGlobalScope('current_team')`.

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
