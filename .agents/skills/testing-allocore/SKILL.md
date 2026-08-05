---
name: Testing Allocore Suite modules
description: How to start the dev server, authenticate, and test module navigation in the allocore-suite Laravel monorepo.
---

# Testing Allocore Suite modules

## Dev server
The standard `php artisan serve` can block on the `/notifications/stream` SSE endpoint. Start it with multiple workers and `--no-reload`:

```bash
PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8000 --no-reload
```

## Cache clearing after branch/routing changes
Module routes and views are cached. Clear all caches when switching branches or after changing module routes/views:

```bash
php artisan route:clear
php artisan route:cache
php artisan view:cache
php artisan config:clear
```

## Test credentials
- Admin: `admin@allocore.test` / `password`
- Demo: `demo@allocore.test` / `password`

## Browser profile isolation
Service workers (`public/sw.js`) and locale cookies can cache the wrong module page across runs. Launch Chrome with a fresh `--user-data-dir` and set the language to English:

```bash
/opt/.devin/chrome/chrome/linux-137.0.7118.2/chrome-linux64/chrome \
  --remote-debugging-port=29229 \
  --user-data-dir=/tmp/chrome_allocore_profile \
  --no-first-run --no-default-browser-check --enable-automation \
  --lang=en-US --accept-lang=en-US,en \
  http://127.0.0.1:8000
```

## Known gotchas
- The login page is a Livewire/Volt component (`pages.auth.login`). It can be fragile with automated typing and may fall back to showing raw translation keys if the `locale` cookie is set to a language without translations.
- Org-detail pages (`/app/orgmatrix/organizations/{id}/roles`, etc.) can return 200 HTML but may be handed to an external editor by the test environment's default application handler. If this happens, verify the route with `curl` and then investigate the Chrome/KDE default application configuration.
- The locale switcher (`partials/locale-switcher`) is a `<select onchange="window.location.href = this.value">`. Automated clicks may not open the dropdown; verify language switches with `curl -e <referer> http://127.0.0.1:8000/language/<locale>` and then reload the target page. `LanguageController` uses `redirect()->back()`, so the `Referer` header must be present for it to return to the correct page.
- If a fresh Chrome profile still loads in German, reset the admin user's `locale` column to `en` before logging in (`User::find(1)->update(['locale' => 'en'])`).
- Module nav is conditionally included in `resources/views/layouts/shell.blade.php` based on `request()->is('app/<module>*')`.
- The redesigned `/dashboard` calls `$allocoreHistory->pluck()` assuming `AllocoreScoreService::historyForTeam()` returns a `Collection`; the service currently returns an `array`, causing a `Call to a member function pluck() on array` error on the dashboard. If testing the new dashboard, either patch `historyForTeam()` to return `Collection` of models or temporarily wrap the variable in `collect()`.
- The PHP dev server can stop responding after heavy UI use; kill all `php` processes and restart with `PHP_CLI_SERVER_WORKERS=4` if requests begin to timeout.
- Chrome address-bar navigation can become unreliable with the `--user-data-dir` profile; for a static public page screenshot, use `chrome --headless --screenshot=<path> <url>` as a fallback.
- Module nested routes may render a stale cached page until a hard refresh (`Ctrl+F5`) due to `public/sw.js`.
- The cookie-consent banner at the bottom of the page may not respond to `computer` tool clicks; it can usually be ignored or accepted manually if it overlaps content.
- For large module route regression passes, an authenticated `curl` sweep is much faster and more reliable than clicking through every route in Chrome. Save the session cookie once with `/test-login`, then run requests with `-b /tmp/cookies.txt` and check for `RouteNotFoundException`/500 text.
- The CDP-managed Chrome tab can become unresponsive after many navigations or a 500 page; if clicks/Return stop working, verify the route with `curl` or restart Chrome.
- The `/notifications/stream` SSE endpoint can also block `chrome --headless --screenshot`, causing it to hang indefinitely. For functional verification of dynamic module forms, an authenticated Laravel feature test (`actingAs($user)->post(...)`) is more reliable than headless screenshots.
- Forms that use Alpine `x-bind:name` for nested array fields (e.g. `steps[0][checklist][0][text]`) cannot be reliably replayed from static HTML with `curl`; use Laravel `TestCase` or parse the database IDs and reconstruct the payload.
