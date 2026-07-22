<?php

namespace Modules\VisionFlow\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->currentTeam) {
            return redirect()->route('teams.index')->with('warning', __('Select a team to use VisionFlow.'));
        }

        return $next($request);
    }
}
