<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieConsentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $consent = $request->cookie('cookie_consent');

        if ($request->has('cookie_consent')) {
            $consent = $request->get('cookie_consent');
            $response = $next($request);
            $response->headers->setCookie(
                cookie('cookie_consent', $consent, 60 * 24 * 365)
            );

            return $response;
        }

        view()->share('cookieConsent', $consent);

        return $next($request);
    }
}
