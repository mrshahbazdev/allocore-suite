# Multi-Tool SaaS Platform — Module Conversion Roadmap (v2)

**Confirmed decisions:**
- **Naya Laravel app** banega (allocore-hub continue nahi hoga — UI/UX naya, modern design)
- **Subscription per-user AND per-team dono** — user/team jis tool ki subscription lega wahi tool use kar sakega
- Core me ek **Analytics/Overview Dashboard** hoga jo sab subscribed tools ka combined summary dikhaye ga

Goal: ek naya Laravel platform jisme har tool ek **module** ho, aur user sirf woh tools use kar sake jin ki us ne **subscription** li ho.

---

## 1. Repo Analysis (Current State)

| Repo | Product | Laravel | Frontend | Key Packages | DB Tables (approx) |
|---|---|---|---|---|---|
| **invoice-maker** | InvoiceMaker — Invoice SaaS (clients, products, invoices, estimates, expenses, cash book, PDF, Stripe) | 11 | Livewire 3 + Blade + Tailwind | dompdf, stripe-php, intervention/image, google2fa, simple-qrcode | ~68 migrations (invoices, invoice_items, clients, products, payments, expenses, plans, tickets, businesses…) |
| **audit** | AuditPro — Business maturity audits (templates, pillars, questions, radar charts, PDF reports) | 12 | Livewire 3 + Volt + Blade | dompdf (via views), Chart.js | 13 migrations (audits, audit_templates, audit_pillars, audit_questions, audit_answers, audit_results, organizations) |
| **keyword-cluster-tool** | ClusterForge — AI keyword/topic cluster generator (Gemini, queued pipeline) | 11 | **React + Inertia** ⚠ | inertia-laravel, ziggy | 8 migrations (projects, subtopics, questions) |
| **lead-quality** | LeadOS — B2B lead gen + AI CRM (ICP scoring, sequences, IMAP inbox scan, kanban pipeline) | 12 | Blade + Tailwind (controllers) | openai-php/client, webklex/laravel-imap | 21 migrations (contacts, activities, sequences, sequence_steps, email_accounts, icp_profiles, teams) |
| **Allocore-Financial-Platform** | Financial KPI analysis (GmbH/Immobilien/Jahresabschluss, PayPal, leads) | 12 | Livewire 4 + Blade | dompdf, phpspreadsheet, spatie/laravel-permission | 17 migrations |

**Important observations:**
- Sab apps standalone hain — har ek ki apni `users` table, apna auth (Breeze), apna layout hai.
- 3 tools Livewire/Blade hain, lekin **keyword-cluster-tool React/Inertia** hai — isko ya to Livewire me rebuild karna hoga ya Inertia ko side-by-side chalana hoga (recommendation: Livewire rebuild, kyunke pipeline backend me hai, UI simple hai).
- Laravel versions mixed (11 vs 12) — host platform **Laravel 12 + Livewire 3** par standardize karein.
- invoice-maker me pehle se `Plan` model + Stripe hai — subscription/billing logic yahan se reuse ho sakta hai.
- Note: aap pehle hi **allocore-hub** repo me audit / invoice-maker / keyword-cluster modules integrate kar chuke hain (PRs #9–#11) — agar wohi platform continue karna hai to yeh roadmap us par bhi apply hota hai.

---

## 2. Target Architecture

### 2.1 Host Platform (Core)
Ek naya Laravel 12 app (ya existing hub) jo provide kare:
- **Central auth**: single `users` table, login/register, 2FA, profile
- **Teams/Companies**: ek central `teams` (ya `companies`) table — har module isi se link ho
- **Roles/Permissions**: `spatie/laravel-permission`
- **Billing & Subscriptions**: `laravel/cashier` (Stripe) — Plans, Prices, trials, invoices
- **Module system**: `nwidart/laravel-modules` package
- Shared UI shell: sidebar/topbar layout, language switcher, notifications

### 2.2 Module Structure (nwidart/laravel-modules)
```
Modules/
  InvoiceMaker/
    Config/, Database/Migrations/, Entities/ (Models),
    Http/Controllers/, Livewire/, Resources/views/,
    Routes/web.php, Providers/InvoiceMakerServiceProvider.php
  Audit/
  KeywordCluster/
  LeadQuality/
```
- Har module apne routes prefix ke sath register kare: `/app/invoices/...`, `/app/audit/...`, `/app/clusters/...`, `/app/leads/...`
- Har module ki tables ko prefix dein (collision se bachne ke liye jahan zaroorat ho): e.g. `km_projects` vs `lq_contacts` — ya kam az kam conflicting names rename karein (`teams`, `organizations`, `plans`, `settings` sab clash karti hain).

### 2.3 Subscription Gating (core mechanism)
```php
// plans table: id, name, stripe_price_id, ...
// plan_module: plan_id, module_key   (kaunsa plan kaunse modules deta hai)
// subscriptions: Cashier standard

// Middleware
Route::middleware(['auth', 'module:invoice-maker'])->group(...);

class EnsureModuleAccess {
  public function handle($request, $next, string $module) {
    if (! $request->user()->currentTeam->hasModule($module)) {
      return redirect()->route('billing.upgrade', ['module' => $module]);
    }
    return $next($request);
  }
}
```
- Sidebar sirf subscribed modules dikhaye; baqi par "Upgrade" badge.
- Admin panel: plans banana, plan↔module mapping, manual grant/revoke.

---

## 3. Per-Module Conversion Steps (repeatable checklist)

Har tool ke liye same process:
1. `php artisan module:make <Name>`
2. **Models** copy → `Modules/<Name>/Entities`, namespace update; `user_id` ko central `users` se, tenant scoping `team_id` se karo (global scope trait: `BelongsToTeam`).
3. **Migrations** copy → module migrations; source repo ki `users/teams/plans/settings` migrations **drop** karo (core provide karega); conflicting table names rename.
4. **Livewire components + Blade views** copy; layout ko core app shell layout par point karo.
5. **Routes** module route file me, `auth + module:<key>` middleware ke sath.
6. Module-specific **config/env** (`GEMINI_API_KEY`, `OPENAI_API_KEY`, IMAP creds) → `Modules/<Name>/Config/config.php`, admin settings UI.
7. **Composer deps** host `composer.json` me merge (dompdf, stripe, openai, imap, intervention…).
8. **Queues/Jobs** (keyword pipeline, sequence sender, inbox scanner) module namespace me move; ek shared queue worker + scheduler.
9. Seeders + test data; smoke test per module.

---

## 4. Phased Roadmap

### Phase 0 — Platform Foundation + New UI/UX (Week 1–2)
- **Naya Laravel 12 app**, nwidart/laravel-modules install
- **Naya UI/UX design system**: modern app shell (sidebar + topbar), Tailwind component library (cards, tables, forms, modals, charts), dark/light mode — sab modules yehi shell use karenge taake look consistent ho
- Central auth + teams + spatie permissions
- **Billing (per-user + per-team)**: Cashier + Stripe; subscription owner ya to `User` hoga ya `Team` (Cashier `Billable` dono par); team subscription = sab team members ko access, user subscription = sirf us user ko
- `module:` middleware — access check: user ki apni subscription **ya** team ki subscription
- Plan↔module mapping + admin panel
- Language system (tools multilingual hain — 10 languages)

### Phase 0.5 — Analytics / Overview Dashboard (core)
- Landing dashboard jo har subscribed module ka summary widget dikhaye:
  - InvoiceMaker: revenue, unpaid invoices, recent invoices
  - Audit: latest audit scores, radar snapshot
  - LeadOS: pipeline value, new leads, sequence stats
  - ClusterForge: projects count, clusters generated is month
- Har module apna widget register kare (`ModuleServiceProvider::registerDashboardWidget()`), dashboard sirf subscribed modules ke widgets render kare
- Cross-tool charts (Chart.js) + quick links

### Phase 1a — Audit module (Week 2–3) ✅ easiest
- Sirf 13 migrations, pure Livewire, koi heavy dependency nahi — pehle isi se pattern set karein
- `organizations` ko core `teams` se map karein

### Phase 1b — Invoice-Maker module (Week 3–4) — biggest
- 68 migrations, sab se zyada features (invoices, estimates, expenses, cash book, client portal, tickets, blog)
- Iska internal `Plan`/Stripe/admin billing **remove** karke core billing use karein
- Client-portal/public-invoice routes public rehne dein (signed URLs)

### Phase 1c — Lead-Quality module (Week 4–5)
- Controllers+Blade → module me move (chaahein to Livewire refactor baad me)
- `teams` table clash: core teams use karein
- IMAP/SMTP per-team email accounts; scheduler jobs (inbox scan, sequence send) register

### Phase 1d — Keyword-Cluster module (Week 5–6)
- Backend pipeline (jobs, Gemini service) as-is move
- UI decision: **React/Inertia → Livewire rebuild** (4–5 screens: projects list, create, detail/progress, download) — ya temporarily Inertia co-exist
- Gemini API key admin settings me

### Phase 2 — Polish & Launch (Week 6–7)
- Plan matrix finalize (e.g. Starter = 1 tool, Pro = all 4), trials, proration
- Usage limits per plan (e.g. clusters/month, contacts count, invoices/month)
- Analytics dashboard finalize (sab modules ke widgets + cross-tool overview)
- QA: subscription gating tests, webhook tests, per-module smoke tests
- Migration script agar existing standalone users ko import karna ho

### Later Phases
- Baqi tools (SmartKPI, Allocore-Financial-Platform, timebutler, PregnancyTracker…) same checklist se modules banain
- API + mobile access via Sanctum, module-scoped tokens

---

## 5. Remaining Decisions
1. keyword-cluster ka React UI → Livewire rebuild (recommended) ya Inertia rakhna hai?
2. Billing: Stripe Cashier ok hai? — ya PayPal bhi chahiye?
3. Table prefixing strategy — conflicting tables: `teams`, `organizations`, `plans`, `settings`, `activities`.
4. Naye repo ka naam?
