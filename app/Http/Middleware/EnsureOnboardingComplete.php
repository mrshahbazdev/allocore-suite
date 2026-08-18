<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Paths that are accessible before onboarding is completed.
     */
    protected array $allowedPaths = [
        'onboarding*',
        'verify-email*',
        'logout',
        'language*',
        'pages*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->onboarding_completed_at && ! $this->isAllowed($request)) {
            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }

    protected function isAllowed(Request $request): bool
    {
        foreach ($this->allowedPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
