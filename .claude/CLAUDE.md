# Allocore Suite — Project Guide for Claude Code

## Project overview
- Laravel 13 + Livewire 3 + Tailwind CSS 3 + Vite monorepo.
- Modular architecture under `Modules/` using `nwidart/laravel-modules`.
- German-first app: default locale is `de`; use `__()` for all user-facing strings.
- Each module has `app/`, `resources/views/`, `routes/`, `database/migrations/`, `config/`, `module.json`.

## Common commands
- Setup: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed`
- Dev server: `php artisan serve` (port 8000) and `npm run dev` (Vite)
- Build assets: `npm run build`
- Lint PHP: `./vendor/bin/pint` or `./vendor/bin/pint --test`
- Run tests: `php artisan test` or `php artisan test --filter=ClusterForge`
- Queue worker: `php artisan queue:work --stop-when-empty --tries=3 --timeout=1200`
- Cache clear: `php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear`

## Architecture conventions
- Modules live in `Modules/{ModuleName}`. Register them in `config/modules.php` `providers` or rely on auto-discovery.
- Livewire components: `app/Livewire/` or `Modules/{Module}/app/Livewire/`; views in `resources/views/livewire/` or module `resources/views/livewire/`.
- Routes: module `routes/web.php` is loaded by the module service provider.
- Models: `app/Models/` or `Modules/{Module}/app/Models/`.
- The global shell layout is `resources/views/layouts/shell.blade.php`.
- Public module nav uses `resources/views/components/module-header.blade.php`.

## Internationalisation
- Default locale is `de` for web requests; tests run with `APP_LOCALE=en`.
- Wrap all UI copy in `__('English key')` and add German values to `lang/de.json` and English values to `lang/en.json`.
- Maturity labels and pillar names are translated the same way.

## Working with modules
1. Create a new module: `php artisan module:make ModuleName`.
2. Add migrations, models, Livewire components, and views under the module directory.
3. Register route service provider in `Modules/{Module}/app/Providers/{Module}ServiceProvider.php`.
4. Seed demo data under `Modules/{Module}/database/seeders/`.

## Testing & quality
- Run `./vendor/bin/pint --test` before every PR.
- Run `php artisan test --filter=ClusterForge` for ClusterForge regression; full suite with `php artisan test`.
- Use the `testing_agent` or `claude` `/run` and `/verify` for end-to-end checks.

## AI providers
- `AiProvider` contract supports OpenAI, Anthropic, and Gemini.
- API keys are stored in `SiteSetting` (`config('services.*')`) or `.env`.
- Use `app/Services/Ai/` and `Modules/ClusterForge/Services/` for provider implementations.

## Shared hosting deploy notes
- Public path on production: `public/`.
- Queue on shared hosts: use `php cron.php` every minute or `php artisan queue:work --stop-when-empty`.
- Cache commands require write access to `storage/` and `bootstrap/cache/`.

## Security
- Never commit `.env`, API keys, or `storage/*.key`.
- Avoid `rm -rf` in scripts and destructive git commands unless explicitly requested.
- Respect module gating (`Team` subscription and pivot permissions) when adding routes.
