<?php

namespace App\Http\Controllers;

class LanguageController extends Controller
{
    public function __invoke(string $locale)
    {
        $locales = config('app.available_locales', ['en', 'de']);

        if (in_array($locale, $locales, true)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}
