<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LanguageController
{
    public function __invoke(string $locale): RedirectResponse
    {
        $supportedLocales = ['en', 'es', 'fr', 'de', 'zh', 'ja', 'pt', 'ru', 'ar', 'hi'];

        if (in_array($locale, $supportedLocales, true)) {
            Session::put('locale', $locale);
        }

        return redirect()->back();
    }
}
