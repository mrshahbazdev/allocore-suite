<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Models\Team;
use Closure;
use Illuminate\Http\Request;

class ResolveTeamBranding
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $main = parse_url(config('app.url'), PHP_URL_HOST) ?? $host;

        if ($host && $host !== $main) {
            $team = Team::where('custom_domain', $host)->orWhere('subdomain', $host)->first();

            if ($team) {
                config(['app.team_branding' => $team->branding() + ['id' => $team->id]]);

                return $next($request);
            }
        }

        config([
            'app.team_branding' => [
                'name' => SiteSetting::value('site_name', config('app.name', 'Allocore Suite')),
                'logo' => SiteSetting::value('site_logo'),
                'favicon' => SiteSetting::value('site_favicon'),
                'primary_color' => SiteSetting::value('primary_color', '#ff9200'),
                'accent_color' => SiteSetting::value('accent_color', '#0094af'),
            ],
        ]);

        return $next($request);
    }
}
