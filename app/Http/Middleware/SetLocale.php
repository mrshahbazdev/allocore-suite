<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('app.available_locales', ['en', 'de']);

        if ($request->has('lang')) {
            $locale = $request->get('lang');

            if (in_array($locale, $locales, true)) {
                session(['locale' => $locale]);
                app()->setLocale($locale);
            }
        } elseif (session()->has('locale')) {
            $locale = session('locale');

            if (in_array($locale, $locales, true)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
