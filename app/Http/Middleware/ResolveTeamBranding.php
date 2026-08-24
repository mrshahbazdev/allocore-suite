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

        $logo = $this->resolveLogo(SiteSetting::value('site_logo'));
        $favicon = $this->resolveFavicon(SiteSetting::value('site_favicon'));

        config([
            'app.team_branding' => [
                'name' => SiteSetting::value('site_name', config('app.name', 'Allocore Suite')),
                'logo' => $logo,
                'favicon' => $favicon,
                'primary_color' => SiteSetting::value('primary_color', '#ff9200'),
                'accent_color' => SiteSetting::value('accent_color', '#0094af'),
            ],
        ]);

        return $next($request);
    }

    private function resolveLogo(?string $configured): ?string
    {
        if (empty($configured)) {
            return '/logo-mark.png';
        }

        if (str_starts_with($configured, 'http') || str_starts_with($configured, '//')) {
            return $configured;
        }

        $path = public_path(ltrim($configured, '/'));

        return is_file($path) ? $configured : '/logo-mark.png';
    }

    private function resolveFavicon(?string $configured): ?string
    {
        if (empty($configured)) {
            return '/favicon.ico';
        }

        if (str_starts_with($configured, 'http') || str_starts_with($configured, '//')) {
            return $configured;
        }

        $path = public_path(ltrim($configured, '/'));

        return is_file($path) ? $configured : '/favicon.ico';
    }
}
