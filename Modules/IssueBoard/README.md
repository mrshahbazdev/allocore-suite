# IssueBoard — Aufgabenboard

Kanban-Board fuer die interne Abstimmung: vier Farbspalten, Bilder und Links pro
Aufgabe, Rueckfragen als Kommentar-Thread, Kontaktdaten des Melders und eine
woechentliche Uebersichts-Mail.

| Spalte | Farbe | Bedeutung |
|---|---|---|
| Neue Aufgabe | rot `#dc2626` | gemeldet, noch nicht angefasst |
| Ali bearbeitet | gelb `#d97706` | liegt bei Ali |
| In Bearbeitung | blau `#2563eb` | liegt bei der Entwicklung |
| Erledigt | gruen `#16a34a` | abgeschlossen |

---

## Installation (nwidart/laravel-modules)

```bash
# 1. Ordner nach Modules/IssueBoard kopieren
cp -r IssueBoard /pfad/zu/allocore/Modules/

# 2. Autoload + Modul aktivieren
composer dump-autoload
php artisan module:enable IssueBoard

# 3. Tabellen anlegen
php artisan migrate

# 4. Uploads erreichbar machen (einmalig)
php artisan storage:link
```

Board liegt dann unter `/aufgabenboard`.

## Installation ohne Modul-Paket (Standard-Laravel)

1. `app/` -> `app/IssueBoard/`, Namespace bleibt `Modules\IssueBoard\` — dafuer in
   der Haupt-`composer.json` ergaenzen:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\IssueBoard\\": "app/IssueBoard/"
    }
}
```

2. Provider in `bootstrap/providers.php` eintragen:

```php
Modules\IssueBoard\Providers\IssueBoardServiceProvider::class,
```

3. `composer dump-autoload && php artisan migrate`

## Konfiguration

```bash
php artisan vendor:publish --tag=issueboard-config
```

In `config/issueboard.php` anpassen:

- `user_model` / `project_model` — Models der Host-App.
- `tenant_column` — Spalte am User fuer Mandantentrennung, `null` wenn nicht genutzt.
  **Wenn Allocore schon einen Tenancy-Trait hat:** in `Issue.php` den Trait
  `BelongsToCurrentTenant` gegen den bestehenden tauschen und
  `app/Support/BelongsToCurrentTenant.php` loeschen.
- `transitions` — welche Rolle welchen Status setzen darf.
- `role_resolver` — Closure, falls Rollen nicht in `users.role` stehen, z. B.:

```php
'role_resolver' => fn ($user) => $user->hasRole('developer') ? 'dev' : 'client',
```

## Woechentliche Uebersicht

Laeuft automatisch ueber den Scheduler (Montag 08:00, per Config aenderbar).
Manuell testen:

```bash
php artisan issueboard:digest --dry-run
php artisan issueboard:digest
```

Voraussetzung: `php artisan schedule:work` bzw. der Cron-Eintrag laeuft, und
fuer die Notifications ein Queue-Worker (`php artisan queue:work`) — alle
Notifications sind `ShouldQueue`.

## Frontend-Abhaengigkeiten

- Tailwind CSS mit `@tailwindcss/forms` (die Inputs nutzen `border-slate-300` etc.)
- Alpine.js (kommt mit Livewire 3)
- SortableJS — im Layout per CDN eingebunden. Lieber lokal:

```bash
npm i sortablejs
```

```js
// resources/js/app.js
import Sortable from 'sortablejs';
window.Sortable = Sortable;
```

Dann das `<script src="https://cdn.jsdelivr.net/...">` aus
`resources/views/layouts/master.blade.php` entfernen.

Tailwind muss die Blade-Dateien des Moduls sehen:

```js
// tailwind.config.js
content: [
    './resources/**/*.blade.php',
    './Modules/**/resources/**/*.blade.php',
],
```

Wenn Allocore bereits ein App-Layout hat: `layouts/master.blade.php` loeschen und
in den drei Livewire-Komponenten `->layout('issueboard::layouts.master')` auf das
eigene Layout aendern.

## Dateien

```
app/Enums/IssueStatus.php          Status + Farben an einer Stelle
app/Models/Issue.php               Kernmodel, moveTo() protokolliert + benachrichtigt
app/Models/IssueComment.php        Threads; Antwort schliesst Rueckfrage automatisch
app/Policies/IssuePolicy.php       Wer darf wohin verschieben
app/Livewire/Board.php             Kanban + Drag-and-drop
app/Livewire/IssueForm.php         Anlegen und Bearbeiten
app/Livewire/IssueDetail.php       Detailansicht, Kommentare, Statuswechsel
app/Console/SendIssueDigest.php    Wochenuebersicht
```

## Naechste Schritte (bewusst weggelassen)

- Rich-Text-Editor (TipTap/Trix) statt Textarea — Beschreibung wird aktuell mit
  `nl2br(e(...))` escaped ausgegeben. **Erst einen Sanitizer einbauen, bevor
  HTML zugelassen wird.**
- Inline-Paste von Screenshots direkt ins Beschreibungsfeld.
- Bild-Thumbnails serverseitig verkleinern (Intervention Image).
