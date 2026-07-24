<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    protected array $exemptRoutes = [
        'two-factor*',
        'logout',
        'logout.*',
        'login',
        'login.*',
        'register',
        'register.*',
        'password.*',
        'two-factor-challenge',
        'two-factor-challenge.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($this->exempt($request)) {
            return $next($request);
        }

        $team = $user->currentTeam;

        if ($team && $team->requires_two_factor && ! $user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.index')->with('status', __('Two-factor authentication is required for this team.'));
        }

        return $next($request);
    }

    protected function exempt(Request $request): bool
    {
        $current = Route::currentRouteName() ?? $request->path();

        foreach ($this->exemptRoutes as $route) {
            if (Str::is($route, $current)) {
                return true;
            }
        }

        return false;
    }
}
