<?php

use App\Models\SiteSetting;
use App\Support\LandingBlocks;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $map = SiteSetting::value('landing_blocks', []);

        if (! is_array($map) || empty($map)) {
            return;
        }

        $base = LandingBlocks::BASE_LOCALE;
        $locales = config('app.available_locales', ['en', 'de']);
        $translatable = LandingBlocks::translatableFields();

        foreach ($map as $blockIndex => $block) {
            if (! is_array($block)) {
                continue;
            }

            foreach ($translatable as $field) {
                if (! isset($block[$field]) || ! is_array($block[$field])) {
                    continue;
                }

                $map[$blockIndex][$field] = $this->normalize($block[$field], $locales, $base);
            }

            if (isset($block['items']) && is_array($block['items'])) {
                foreach ($block['items'] as $itemIndex => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    foreach ($translatable as $field) {
                        if (! isset($item[$field]) || ! is_array($item[$field])) {
                            continue;
                        }

                        $map[$blockIndex]['items'][$itemIndex][$field] = $this->normalize($item[$field], $locales, $base);
                    }
                }
            }
        }

        SiteSetting::setGlobal('landing_blocks', $map);
    }

    private function normalize(array $valueMap, array $locales, string $base): array
    {
        $baseValue = $valueMap[$base] ?? '';

        if (blank($baseValue)) {
            foreach ($locales as $locale) {
                if ($locale === $base) {
                    continue;
                }

                if (filled($valueMap[$locale] ?? '')) {
                    $baseValue = $valueMap[$locale];
                    break;
                }
            }
        }

        $normalized = [$base => $baseValue];

        foreach ($locales as $locale) {
            if ($locale === $base) {
                continue;
            }

            $normalized[$locale] = '';
        }

        return $normalized;
    }

    public function down(): void
    {
        // Not reversible without losing per-locale overrides.
    }
};
