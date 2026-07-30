<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class LandingBlockDefaults
{
    public static function blocks(): array
    {
        return [
            self::hero(),
            self::stats(),
            self::features(),
            self::steps(),
            self::testimonials(),
            self::cta(),
        ];
    }

    private static function hero(): array
    {
        return [
            'type' => 'hero',
            'enabled' => true,
            'heading' => __('landing.hero.heading'),
            'subheading' => __('landing.hero.subheading'),
            'image' => '',
            'cta_text' => __('landing.hero.cta_primary'),
            'cta_url' => Route::has('register') ? route('register') : url('/register'),
            'style' => [
                'bg' => '#0f172a',
                'text_color' => '#ffffff',
                'text_align' => 'center',
                'padding' => 'large',
                'container' => 'max-w-5xl',
                'rounded' => true,
                'border' => false,
            ],
            'layout' => ['columns' => 0, 'gap' => 'medium', 'align' => 'stretch'],
            'animation' => 'fade-up',
        ];
    }

    private static function stats(): array
    {
        return [
            'type' => 'stats',
            'enabled' => true,
            'title' => '',
            'items' => [
                ['label' => __('landing.stats.modules'), 'value' => '', 'suffix' => '', 'source' => 'module_count'],
                ['label' => __('landing.stats.teams'), 'value' => '', 'suffix' => '', 'source' => 'team_count'],
                ['label' => __('landing.stats.users'), 'value' => '', 'suffix' => '', 'source' => 'user_count'],
                ['label' => __('landing.stats.subscriptions'), 'value' => '', 'suffix' => '', 'source' => 'active_subscription_count'],
                ['label' => __('landing.stats.audits'), 'value' => '', 'suffix' => '', 'source' => 'audit_count'],
                ['label' => __('landing.stats.avg_allocore_score'), 'value' => '', 'suffix' => '', 'source' => 'avg_allocore_score'],
            ],
            'style' => [
                'bg' => '#0f172a',
                'text_color' => '#ffffff',
                'text_align' => 'center',
                'padding' => 'medium',
                'container' => 'max-w-7xl',
                'rounded' => false,
                'border' => false,
            ],
            'layout' => ['columns' => 3, 'gap' => 'medium', 'align' => 'stretch'],
            'animation' => 'fade-in',
        ];
    }

    private static function features(): array
    {
        return [
            'type' => 'features',
            'enabled' => true,
            'title' => __('landing.features.heading'),
            'items' => [
                ['title' => __('landing.features.auth.title'), 'description' => __('landing.features.auth.desc')],
                ['title' => __('landing.features.teams.title'), 'description' => __('landing.features.teams.desc')],
                ['title' => __('landing.features.billing.title'), 'description' => __('landing.features.billing.desc')],
                ['title' => __('landing.features.analytics.title'), 'description' => __('landing.features.analytics.desc')],
            ],
            'style' => [
                'bg' => '',
                'text_color' => '',
                'text_align' => 'center',
                'padding' => 'large',
                'container' => 'max-w-7xl',
                'rounded' => true,
                'border' => true,
            ],
            'layout' => ['columns' => 4, 'gap' => 'medium', 'align' => 'stretch'],
            'animation' => 'fade-up',
        ];
    }

    private static function steps(): array
    {
        return [
            'type' => 'steps',
            'enabled' => true,
            'title' => __('landing.how.heading'),
            'items' => [
                ['title' => __('landing.how.step1.title'), 'description' => __('landing.how.step1.desc')],
                ['title' => __('landing.how.step2.title'), 'description' => __('landing.how.step2.desc')],
                ['title' => __('landing.how.step3.title'), 'description' => __('landing.how.step3.desc')],
            ],
            'style' => [
                'bg' => '',
                'text_color' => '',
                'text_align' => 'center',
                'padding' => 'large',
                'container' => 'max-w-5xl',
                'rounded' => false,
                'border' => false,
            ],
            'layout' => ['columns' => 3, 'gap' => 'large', 'align' => 'stretch'],
            'animation' => 'zoom-in',
        ];
    }

    private static function testimonials(): array
    {
        return [
            'type' => 'testimonials',
            'enabled' => true,
            'title' => __('landing.testimonials.heading'),
            'items' => [
                ['quote' => __('landing.testimonials.quote'), 'author' => __('landing.testimonials.author'), 'role' => ''],
            ],
            'style' => [
                'bg' => '',
                'text_color' => '',
                'text_align' => 'center',
                'padding' => 'large',
                'container' => 'max-w-3xl',
                'rounded' => true,
                'border' => true,
            ],
            'layout' => ['columns' => 1, 'gap' => 'medium', 'align' => 'stretch'],
            'animation' => 'fade-in',
        ];
    }

    private static function cta(): array
    {
        return [
            'type' => 'cta',
            'enabled' => true,
            'title' => __('landing.cta.heading'),
            'text' => __('landing.cta.subheading'),
            'button_text' => __('landing.cta.primary'),
            'button_url' => Route::has('register') ? route('register') : url('/register'),
            'style' => [
                'bg' => '#4f46e5',
                'text_color' => '#ffffff',
                'text_align' => 'center',
                'padding' => 'large',
                'container' => 'max-w-4xl',
                'rounded' => true,
                'border' => false,
            ],
            'layout' => ['columns' => 0, 'gap' => 'medium', 'align' => 'stretch'],
            'animation' => 'fade-up',
        ];
    }
}
