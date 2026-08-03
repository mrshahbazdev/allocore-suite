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

    private static function t(string $key): array
    {
        $locales = config('app.available_locales', ['en', 'de']);
        $values = [];

        foreach ($locales as $locale) {
            $values[$locale] = __($key, [], $locale);
        }

        return $values;
    }

    private static function hero(): array
    {
        return [
            'type' => 'hero',
            'enabled' => true,
            'heading' => self::t('landing.hero.heading'),
            'subheading' => self::t('landing.hero.subheading'),
            'image' => '',
            'cta_text' => self::t('landing.hero.cta_primary'),
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
                ['label' => self::t('landing.stats.modules'), 'value' => '', 'suffix' => '', 'source' => 'module_count'],
                ['label' => self::t('landing.stats.teams'), 'value' => '', 'suffix' => '', 'source' => 'team_count'],
                ['label' => self::t('landing.stats.users'), 'value' => '', 'suffix' => '', 'source' => 'user_count'],
                ['label' => self::t('landing.stats.subscriptions'), 'value' => '', 'suffix' => '', 'source' => 'active_subscription_count'],
                ['label' => self::t('landing.stats.audits'), 'value' => '', 'suffix' => '', 'source' => 'audit_count'],
                ['label' => self::t('landing.stats.avg_allocore_score'), 'value' => '', 'suffix' => '', 'source' => 'avg_allocore_score'],
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
            'title' => self::t('landing.features.heading'),
            'items' => [
                ['title' => self::t('landing.features.auth.title'), 'description' => self::t('landing.features.auth.desc')],
                ['title' => self::t('landing.features.teams.title'), 'description' => self::t('landing.features.teams.desc')],
                ['title' => self::t('landing.features.billing.title'), 'description' => self::t('landing.features.billing.desc')],
                ['title' => self::t('landing.features.analytics.title'), 'description' => self::t('landing.features.analytics.desc')],
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
            'title' => self::t('landing.how.heading'),
            'items' => [
                ['title' => self::t('landing.how.step1.title'), 'description' => self::t('landing.how.step1.desc')],
                ['title' => self::t('landing.how.step2.title'), 'description' => self::t('landing.how.step2.desc')],
                ['title' => self::t('landing.how.step3.title'), 'description' => self::t('landing.how.step3.desc')],
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
            'title' => self::t('landing.testimonials.heading'),
            'items' => [
                ['quote' => self::t('landing.testimonials.quote'), 'author' => self::t('landing.testimonials.author'), 'role' => ''],
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
            'title' => self::t('landing.cta.heading'),
            'text' => self::t('landing.cta.subheading'),
            'button_text' => self::t('landing.cta.primary'),
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
