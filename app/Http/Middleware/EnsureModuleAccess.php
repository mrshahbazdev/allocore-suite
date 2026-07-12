<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasModule($moduleKey)) {
            return redirect()
                ->route('billing.plans', ['module' => $moduleKey])
                ->with('warning', __('You need an active subscription to access this tool.'));
        }

        return $next($request);
    }
}
