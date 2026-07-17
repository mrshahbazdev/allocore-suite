<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SiteSetting::value('maintenance_mode') && ! $request->user()?->hasRole('admin')) {
            if (! $request->is('admin/*') && ! $request->is('login') && ! $request->is('logout')) {
                abort(503, SiteSetting::value('maintenance_message', __('Service temporarily unavailable.')));
            }
        }

        return $next($request);
    }
}
