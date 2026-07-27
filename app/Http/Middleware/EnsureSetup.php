<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('admin.setup.*') || $request->routeIs('onboarding.*') || $request->routeIs('install.*') || $request->routeIs('logout') || $request->routeIs('logout.*') || ! $request->user()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user->hasRole('admin') && ! SiteSetting::value('setup_wizard_completed', false)) {
            return redirect()->route('admin.setup.index');
        }

        return $next($request);
    }
}
