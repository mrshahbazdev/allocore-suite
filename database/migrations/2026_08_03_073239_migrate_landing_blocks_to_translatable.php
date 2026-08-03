<?php

use App\Models\SiteSetting;
use App\Support\LandingBlocks;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('site_settings')
            ->where('key', 'like', 'landing_blocks\_%')
            ->orWhere('key', 'landing_blocks')
            ->get(['key', 'value']);

        $perLocale = [];

        foreach ($rows as $row) {
            $key = $row->key;
            $value = json_decode($row->value, true);
            if ($value === null && $row->value !== 'null') {
                $value = [];
            }

            if ($key === 'landing_blocks') {
                $perLocale[LandingBlocks::BASE_LOCALE] = $value;
            } else {
                $locale = str_replace('landing_blocks_', '', $key);
                if (in_array($locale, config('app.available_locales', ['en', 'de']), true)) {
                    $perLocale[$locale] = $value;
                }
            }
        }

        if (empty($perLocale)) {
            return;
        }

        $baseLocale = LandingBlocks::BASE_LOCALE;
        $base = $perLocale[$baseLocale] ?? array_values($perLocale)[0];

        $map = [];
        foreach ($base as $index => $block) {
            $map[$index] = $this->asMap($block, $baseLocale);
        }

        foreach ($perLocale as $locale => $blocks) {
            if ($locale === $baseLocale) {
                continue;
            }

            foreach ($blocks as $index => $block) {
                $map[$index] = $this->mergeMap($map[$index] ?? $this->asMap($block, $baseLocale), $block, $locale, $baseLocale);
            }
        }

        SiteSetting::setGlobal('landing_blocks', $map);
    }

    public function down(): void
    {
        // Reverting would lose the per-locale overrides; we keep the global map.
    }

    private function asMap(array $block, string $base): array
    {
        foreach (LandingBlocks::translatableFields() as $field) {
            if (array_key_exists($field, $block) && ! is_array($block[$field])) {
                $block[$field] = [$base => is_string($block[$field]) ? $block[$field] : ''];
            }
        }

        if (isset($block['items']) && is_array($block['items'])) {
            foreach ($block['items'] as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }
                foreach (LandingBlocks::translatableFields() as $field) {
                    if (array_key_exists($field, $item) && ! is_array($item[$field])) {
                        $item[$field] = [$base => is_string($item[$field]) ? $item[$field] : ''];
                    }
                }
                $block['items'][$i] = $item;
            }
        }

        return $block;
    }

    private function mergeMap(array $map, array $override, string $locale, string $base): array
    {
        foreach (LandingBlocks::translatableFields() as $field) {
            if (! array_key_exists($field, $override)) {
                continue;
            }

            $overrideValue = is_string($override[$field]) ? $override[$field] : '';
            $baseValue = is_array($map[$field] ?? '') ? ($map[$field][$base] ?? '') : ($map[$field] ?? '');

            if (blank($map[$field] ?? '')) {
                $map[$field] = [$base => $overrideValue];
            }

            if ($overrideValue !== $baseValue) {
                if (! is_array($map[$field])) {
                    $map[$field] = [$base => $map[$field] ?? ''];
                }
                $map[$field][$locale] = $overrideValue;
            }
        }

        if (isset($override['items']) && is_array($override['items'])) {
            if (! isset($map['items']) || ! is_array($map['items'])) {
                $map['items'] = [];
            }

            foreach ($override['items'] as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $mapItem = $map['items'][$i] ?? [];
                foreach (LandingBlocks::translatableFields() as $field) {
                    if (! array_key_exists($field, $item)) {
                        continue;
                    }

                    $overrideValue = is_string($item[$field]) ? $item[$field] : '';
                    $baseValue = is_array($mapItem[$field] ?? '') ? ($mapItem[$field][$base] ?? '') : ($mapItem[$field] ?? '');

                    if (blank($mapItem[$field] ?? '')) {
                        $mapItem[$field] = [$base => $overrideValue];
                    }

                    if ($overrideValue !== $baseValue) {
                        if (! is_array($mapItem[$field])) {
                            $mapItem[$field] = [$base => $mapItem[$field] ?? ''];
                        }
                        $mapItem[$field][$locale] = $overrideValue;
                    }
                }
                $map['items'][$i] = $mapItem;
            }
        }

        return $map;
    }
};
