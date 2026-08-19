---
name: design
description: Apply Allocore Suite UI/UX conventions, Tailwind patterns, brand identity, and component standards. Use when the user asks to design, style, polish, refactor, or build a Blade/Livewire view, component, or page.
---

# Allocore Suite Design Guide

## Brand identity
- **Primary accent:** `#ff9200` (mapped to `indigo-500` and `brand-primary` in Tailwind).
- **Secondary accent:** `#0094af` (mapped to `blue-600` and `brand-secondary`).
- **Dark/heading text:** `#1f2937` (slate-800 equivalent) or `slate-900`.
- **Body text:** `slate-600` or `txmain` (`#4a4a4a`).
- **Background:** `bg-slate-50` for app pages; `page` (`#ffffff`) for cards.
- **Card background:** `bg-white` with `rounded-2xl border border-slate-200 shadow-sm`.

## Tailwind configuration
- File: `tailwind.config.js`.
- Font: `font-sans` resolves to `Figtree`.
- `darkMode: 'class'` is configured, but the app currently uses a light-first UI unless the user explicitly adds a dark mode toggle.
- The `brand`, `indigo`, and `blue` color palettes are overridden to the Allocore orange/blue identity. Prefer `indigo-500`, `brand-primary`, `brand-secondary`, or direct `bg-[#ff9200]` / `text-[#ff9200]`.

## Layout and spacing
- Page wrapper: `space-y-6` on the main container; `p-4 sm:p-6 lg:p-8` on content areas.
- Content width: `max-w-7xl mx-auto` for full views; `max-w-3xl` or `max-w-4xl` for forms.
- Grid for stat cards: `grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4`.
- Module pages start with the `module-header` partial and a `module-nav` tab bar.

## Reusable component classes
Define UI with these custom classes in `resources/css/app.css`:
- `.card` — white card with rounded corners and shadow.
- `.card-title` — section title inside a card.
- `.form-grid` — 1-col on mobile, 2-col on `sm+`.
- `.form-label` / `.form-control` — form inputs; focus rings use `#ff9200`.
- `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-success`, `.btn-sm`.
- `.badge`, `.badge-green`, `.badge-yellow`, `.badge-red`, `.badge-gray`, `.badge-admin`, `.badge-user`, `.badge-owner`, `.badge-member`.
- `.data-table` — tables with alternating row backgrounds.
- Score helpers: `.score-lg`, `.score-green`, `.score-yellow`, `.score-red`.

Prefer these classes over inline Tailwind when they match. Use inline utilities for one-off spacing or layout.

## Module header pattern
When building a module page, include:
```blade
@include('partials.module-header')
```
Then add a tab navigation component (`components.tabs` or module-specific tabs) and the page content.

## Buttons
- Primary actions: `.btn.btn-primary` (`bg-[#ff9200]`).
- Secondary / cancel: `.btn.btn-secondary`.
- Destructive: `.btn.btn-danger`.
- Success / save: `.btn.btn-success`.
- Use `inline-flex items-center justify-center gap-2` with an SVG icon when adding an icon.

## Cards and containers
- Cards: `bg-white rounded-2xl border border-slate-200 p-5 shadow-sm sm:p-6`.
- Hover lift: any `bg-white rounded-xl` card gets a subtle `translateY(-0.25rem)` and shadow on hover (defined in `app.css`).
- Avoid hardcoded gradient backgrounds; the brand relies on flat, clean surfaces.

## Forms
- Wrap fields in `.form-grid`.
- Labels: `.form-label`.
- Inputs: `.form-control` (`block w-full rounded-lg border-slate-300 shadow-sm`).
- Focus: `border-[#ff9200] ring-[#ff9200]`.
- Validation errors: small red text under the field, `text-rose-600`.

## Tables and lists
- Use `.data-table` for index/list pages.
- Empty states: centered card with an icon and a clear CTA button.
- Pagination: Laravel's default Tailwind view is acceptable; add `mt-6` spacing.

## Icons
- Use inline SVGs from Heroicons-style paths (`24` viewBox, `stroke-width="2"`, `stroke="currentColor"`).
- Icon size: `h-5 w-5` for buttons, `h-4 w-4` for inline text, `h-6 w-6` for stats.

## Animations
- Use `reveal reveal-fade-up` utilities for scroll-triggered entrance animations.
- Buttons have built-in `scale` transitions via `app.css`.
- Keep animations subtle and consistent; avoid heavy motion that slows the UI.

## Internationalization
- All user-facing text must be wrapped in `__()`: `{{ __('English key') }}`.
- Add German translations to `lang/de.json` and English to `lang/en.json`.
- German is the default web locale; tests use `APP_LOCALE=en`.

## Responsive rules
- Mobile-first: build the narrow layout first, then add `sm:`, `md:`, `lg:` breakpoints.
- Sidebar behavior: module pages may hide the dashboard sidebar for a separate-page feel; follow the existing `shell` layout conventions.

## Accessibility
- Use semantic HTML (`<button>` for actions, `<a>` for navigation).
- Form inputs need associated `<label>` with `for` attribute.
- Color contrast: orange on white passes only at large sizes; use `bg-[#ff9200]` with `text-white`, not orange text on white backgrounds for body copy.

## What to avoid
- Do not use the default Tailwind `indigo` or `blue` palettes as if they are the original colors; they are remapped to brand colors.
- Do not introduce new CSS frameworks; stay within Tailwind + `app.css` custom components.
- Do not hardcode English strings in views.
- Do not add heavy gradients or shadows beyond the established system.

## When designing a new module
1. Create the module shell with `module-header` and tab nav.
2. Build a dashboard/index card grid with stat tiles.
3. Add list views with `.data-table`.
4. Add create/edit forms with `.form-grid`.
5. Apply `.btn`, `.badge`, and `.card` classes consistently.
6. Wrap all strings in `__()` and update language JSON files.
