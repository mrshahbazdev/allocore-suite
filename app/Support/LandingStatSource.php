<?php

namespace App\Support;

use App\Models\AllocoreScore;
use App\Models\Module;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Modules\AuditPro\Models\Audit;

class LandingStatSource
{
    private static array $sources = [
        '' => 'Manual / custom',
        'module_count' => 'Active modules',
        'team_count' => 'Teams',
        'user_count' => 'Registered users',
        'active_subscription_count' => 'Active subscriptions',
        'audit_count' => 'Completed audits',
        'avg_allocore_score' => 'Average Allocore Score',
    ];

    public static function options(): array
    {
        return self::$sources;
    }

    public static function value(string $source, string $default = ''): string
    {
        return match ($source) {
            'module_count' => (string) Module::where('is_active', true)->count(),
            'team_count' => (string) Team::count(),
            'user_count' => (string) User::count(),
            'active_subscription_count' => (string) self::activeSubscriptions(),
            'audit_count' => (string) self::auditCount(),
            'avg_allocore_score' => self::averageAllocoreScore(),
            default => $default,
        };
    }

    private static function activeSubscriptions(): int
    {
        return ToolSubscription::where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->count();
    }

    private static function auditCount(): int
    {
        if (class_exists(Audit::class)) {
            return Audit::count();
        }

        return AllocoreScore::whereNotNull('audit_id')->count();
    }

    private static function averageAllocoreScore(): string
    {
        $avg = AllocoreScore::avg('score');

        return $avg === null ? '0' : (string) round((float) $avg, 1);
    }
}
