<?php

namespace Modules\AuditIntelligence\Services;

use App\Models\SiteSetting;

class ManagerSsoLink
{
    /**
     * Signed single-sign-on URL into the Allocore Manager — the Manager
     * creates/links the user from this login data (HMAC, 10-minute validity).
     */
    public static function url($user, ?string $company = null): ?string
    {
        $base = SiteSetting::value('manager.url') ?: config('services.manager.url');
        $secret = SiteSetting::value('manager.sso_secret') ?: config('services.manager.sso_secret');

        if (! is_string($base) || ! is_string($secret) || $base === '' || $secret === '') {
            return null;
        }

        $params = [
            'email' => $user->email,
            'name' => $user->name,
            'company' => $company ?? $user->currentTeam?->name ?? '',
            'role' => 'member',
            'ts' => time(),
        ];
        $params['sig'] = hash_hmac('sha256', implode('|', $params), $secret);

        return rtrim($base, '/').'/sso/allocore?'.http_build_query($params);
    }
}
