<?php

namespace Modules\LeadQuality\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->currentTeam) {
            return redirect()
                ->route('teams.index')
                ->with('warning', __('Create or select a team before using LeadOS.'));
        }

        return $next($request);
    }
}
