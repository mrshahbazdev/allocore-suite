<?php

namespace Modules\DevManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->current_team_id) {
            return redirect()->route('teams.index')->with('warning', __('Please select a team first.'));
        }

        $idea = $request->route('idea');

        if ($idea && $idea->team_id !== $user->current_team_id) {
            abort(403);
        }

        return $next($request);
    }
}
