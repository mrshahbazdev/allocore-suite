<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::whereHas('translations', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->first();

        if (! $page || ! $page->is_published) {
            abort(404);
        }

        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');

        $translation = $page->translations->firstWhere('locale', $locale)
            ?? $page->translations->firstWhere('locale', $fallback);

        if (! $translation) {
            abort(404);
        }

        $alternates = $page->translations
            ->mapWithKeys(fn ($t) => [$t->locale => route('page.show', $t->slug)])
            ->toArray();

        return view('pages.show', compact('page', 'translation', 'alternates'));
    }
}
