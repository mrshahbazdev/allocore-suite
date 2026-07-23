<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.installed') && ! $request->is('install', 'install/*')) {
            return redirect()->route('install.index');
        }

        if (config('app.installed') && $request->is('install', 'install/*')) {
            return redirect('/');
        }

        return $next($request);
    }
}
