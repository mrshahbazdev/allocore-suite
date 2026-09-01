<?php

namespace App\Support;

class LandingBlock
{
    public static function settings(array $block): array
    {
        $style = is_array($block['style'] ?? null) ? $block['style'] : [];
        $layout = is_array($block['layout'] ?? null) ? $block['layout'] : [];

        $padding = in_array($style['padding'] ?? '', ['small', 'medium', 'large']) ? $style['padding'] : 'medium';
        $container = in_array($style['container'] ?? '', ['default', 'max-w-7xl', 'max-w-5xl', 'max-w-3xl', 'full']) ? $style['container'] : 'default';
        $textAlign = in_array($style['text_align'] ?? '', ['left', 'center', 'right']) ? $style['text_align'] : 'center';
        $rounded = filter_var($style['rounded'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $border = filter_var($style['border'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $animation = $block['animation'] ?? '';

        $paddingClass = match ($padding) {
            'small' => 'py-10 lg:py-14',
            'large' => 'py-24 lg:py-32',
            default => 'py-16 lg:py-24',
        };

        $containerClass = match ($container) {
            'max-w-7xl' => 'max-w-7xl mx-auto px-6 lg:px-8',
            'max-w-5xl' => 'max-w-5xl mx-auto px-6 lg:px-8',
            'max-w-3xl' => 'max-w-3xl mx-auto px-6 lg:px-8',
            'full' => 'px-6 lg:px-8',
            default => '',
        };

        $gap = $layout['gap'] ?? 'medium';
        $gapClass = match ($gap) {
            'small' => 'gap-4',
            'large' => 'gap-10',
            default => 'gap-6',
        };

        $columns = (int) ($layout['columns'] ?? 0);
        $columnsClass = match ($columns) {
            1 => 'grid-cols-1',
            2 => 'grid-cols-1 sm:grid-cols-2',
            3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
            4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
            default => '',
        };

        $align = in_array($layout['align'] ?? '', ['start', 'center', 'end', 'stretch']) ? $layout['align'] : 'stretch';
        $alignClass = 'items-'.$align;

        $isDarkBg = false;
        if (filled($style['bg'] ?? '')) {
            $hex = ltrim(trim($style['bg']), '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            if (strlen($hex) === 6 && ctype_xdigit($hex)) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                $isDarkBg = $luminance < 0.65;
            }
        }

        $bgStyle = filled($style['bg'] ?? '') ? 'background-color: '.e($style['bg']).';' : '';
        if (filled($style['text_color'] ?? '')) {
            $textStyle = 'color: '.e($style['text_color']).';';
        } elseif ($isDarkBg) {
            $textStyle = 'color: #f8fafc;';
        } else {
            $textStyle = '';
        }
        $inlineStyle = trim($bgStyle.' '.$textStyle);

        return [
            'padding_class' => $paddingClass,
            'container_class' => $containerClass,
            'text_align_class' => 'text-'.$textAlign,
            'rounded_class' => $rounded ? 'rounded-2xl' : '',
            'border_class' => $border ? 'border border-slate-200' : '',
            'animation_class' => $animation ? 'reveal reveal-'.$animation : '',
            'inline_style' => $inlineStyle,
            'is_dark_bg' => $isDarkBg,
            'gap_class' => $gapClass,
            'columns_class' => $columnsClass,
            'align_class' => $alignClass,
        ];
    }
}
