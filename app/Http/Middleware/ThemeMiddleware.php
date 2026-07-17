<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThemeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $theme = $request->cookie('theme') ?? 'light';

        if ($request->user()?->theme) {
            $theme = $request->user()->theme;
        }

        if (! in_array($theme, ['light', 'dark'], true)) {
            $theme = 'light';
        }

        view()->share('theme', $theme);

        $response = $next($request);

        return $response;
    }
}
