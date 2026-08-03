<?php

namespace App\Support;

use App\Models\SiteSetting;

class LandingBlocks
{
    public const BASE_LOCALE = 'de';

    public static function forPublic(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return self::resolve(self::raw(), $locale);
    }

    public static function forAdmin(string $locale): array
    {
        return self::resolve(self::raw(), $locale);
    }

    public static function raw(): array
    {
        $blocks = SiteSetting::value('landing_blocks', []);

        if (empty($blocks)) {
            $blocks = LandingBlockDefaults::blocks();
        }

        return self::normalize($blocks);
    }

    public static function save(array $submitted, string $locale): void
    {
        $map = self::raw();
        $map = self::merge($map, $submitted, $locale);

        SiteSetting::setGlobal('landing_blocks', $map);
    }

    public static function translatableFields(): array
    {
        return [
            'heading', 'subheading', 'cta_text', 'title', 'text', 'button_text', 'content',
            'alt', 'question', 'answer', 'label', 'suffix', 'value', 'quote', 'author', 'role',
            'name', 'price', 'period', 'features', 'description',
        ];
    }

    public static function resolve(array $blocks, string $locale, string $base = self::BASE_LOCALE): array
    {
        return array_map(fn ($block) => self::resolveBlock($block, $locale, $base), $blocks);
    }

    public static function normalize(array $blocks): array
    {
        return array_values(array_map(fn ($block) => self::normalizeBlock($block), $blocks));
    }

    public static function merge(array $map, array $submitted, string $locale, string $base = self::BASE_LOCALE): array
    {
        foreach ($submitted as $index => $block) {
            if (! is_array($block) || ! isset($block['type'])) {
                continue;
            }

            $map[$index] = $map[$index] ?? ['type' => $block['type']];
            $map[$index] = self::mergeBlock($map[$index], $block, $locale, $base);
        }

        // Re-index so enabled flags/order stay consistent.
        return array_values($map);
    }

    private static function normalizeBlock(array $block): array
    {
        foreach (self::translatableFields() as $field) {
            if (array_key_exists($field, $block) && ! is_array($block[$field])) {
                $value = is_string($block[$field]) ? $block[$field] : '';
                $block[$field] = [self::BASE_LOCALE => $value];
            }
        }

        if (isset($block['items']) && is_array($block['items'])) {
            foreach ($block['items'] as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }
                foreach (self::translatableFields() as $field) {
                    if (array_key_exists($field, $item) && ! is_array($item[$field])) {
                        $value = is_string($item[$field]) ? $item[$field] : '';
                        $item[$field] = [self::BASE_LOCALE => $value];
                    }
                }
                $block['items'][$i] = $item;
            }
        }

        return $block;
    }

    private static function resolveBlock(array $block, string $locale, string $base): array
    {
        foreach (self::translatableFields() as $field) {
            if (array_key_exists($field, $block)) {
                $block[$field] = self::text($block[$field], $locale, $base);
            }
        }

        if (isset($block['items']) && is_array($block['items'])) {
            foreach ($block['items'] as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }
                foreach (self::translatableFields() as $field) {
                    if (array_key_exists($field, $item)) {
                        $item[$field] = self::text($item[$field], $locale, $base);
                    }
                }
                $block['items'][$i] = $item;
            }
        }

        return $block;
    }

    private static function text(mixed $value, string $locale, string $base): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return '';
        }

        foreach ([$locale, $base] as $key) {
            if (filled($value[$key] ?? '')) {
                return (string) $value[$key];
            }
        }

        // Fallback to any non-empty available translation.
        foreach ($value as $translation) {
            if (filled($translation)) {
                return (string) $translation;
            }
        }

        return '';
    }

    private static function mergeBlock(array $map, array $submitted, string $locale, string $base): array
    {
        foreach (['type', 'enabled', 'style', 'layout', 'animation'] as $key) {
            if (array_key_exists($key, $submitted)) {
                $map[$key] = $submitted[$key];
            }
        }

        foreach (self::translatableFields() as $field) {
            if (! array_key_exists($field, $submitted)) {
                continue;
            }

            if (! is_array($map[$field] ?? '')) {
                $map[$field] = [];
            }

            $submittedValue = is_string($submitted[$field]) ? $submitted[$field] : '';

            if ($locale === $base) {
                // Editing the baseline language.
                $map[$field][$base] = $submittedValue;

                // If no other locale has an explicit override, keep them in sync
                // so the baseline change is visible everywhere until translated.
                foreach ($map[$field] as $l => $v) {
                    if ($l !== $base && blank($v)) {
                        $map[$field][$l] = $submittedValue;
                    }
                }
            } else {
                // Editing an override. Store empty string to fall back to baseline.
                $baseValue = $map[$field][$base] ?? '';

                if (blank($submittedValue) || $submittedValue === $baseValue) {
                    $map[$field][$locale] = '';
                } else {
                    $map[$field][$locale] = $submittedValue;
                }

                // Ensure a baseline exists for fallback if none was set.
                if (! array_key_exists($base, $map[$field]) || blank($map[$field][$base])) {
                    $map[$field][$base] = $submittedValue;
                }
            }
        }

        if (isset($submitted['items']) && is_array($submitted['items'])) {
            $map['items'] = [];
            foreach ($submitted['items'] as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $mapItem = $map['items'][$i] ?? [];
                foreach (self::translatableFields() as $field) {
                    if (! array_key_exists($field, $item)) {
                        continue;
                    }
                    if (! is_array($mapItem[$field] ?? '')) {
                        $mapItem[$field] = [];
                    }

                    $submittedValue = is_string($item[$field]) ? $item[$field] : '';

                    if ($locale === $base) {
                        $mapItem[$field][$base] = $submittedValue;
                        foreach ($mapItem[$field] as $l => $v) {
                            if ($l !== $base && blank($v)) {
                                $mapItem[$field][$l] = $submittedValue;
                            }
                        }
                    } else {
                        $baseValue = $mapItem[$field][$base] ?? '';

                        if (blank($submittedValue) || $submittedValue === $baseValue) {
                            $mapItem[$field][$locale] = '';
                        } else {
                            $mapItem[$field][$locale] = $submittedValue;
                        }

                        if (! array_key_exists($base, $mapItem[$field]) || blank($mapItem[$field][$base])) {
                            $mapItem[$field][$base] = $submittedValue;
                        }
                    }
                }
                $map['items'][$i] = $mapItem;
            }
        }

        return $map;
    }
}
