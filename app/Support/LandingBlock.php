<?php

namespace App\Support;

class LandingBlock
{
    public static function settings(array $block): array
    {
        $style = is_array($block['style'] ?? null) ? $block['style'] : [];
        $padding = in_array($style['padding'] ?? '', ['small', 'medium', 'large']) ? $style['padding'] : 'medium';
        $container = in_array($style['container'] ?? '', ['default', 'max-w-7xl', 'max-w-5xl', 'max-w-3xl', 'full']) ? $style['container'] : 'default';
        $textAlign = in_array($style['text_align'] ?? '', ['left', 'center', 'right']) ? $style['text_align'] : 'center';
        $rounded = filter_var($style['rounded'] ?? false, FILTER_VALIDATE_BOOLEAN);
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

        $bgStyle = filled($style['bg'] ?? '') ? 'background-color: '.e($style['bg']).';' : '';
        $textStyle = filled($style['text_color'] ?? '') ? 'color: '.e($style['text_color']).';' : '';
        $inlineStyle = trim($bgStyle.' '.$textStyle);

        return [
            'padding_class' => $paddingClass,
            'container_class' => $containerClass,
            'text_align_class' => 'text-'.$textAlign,
            'rounded_class' => $rounded ? 'rounded-2xl' : '',
            'animation_class' => $animation ? 'reveal reveal-'.$animation : '',
            'inline_style' => $inlineStyle,
        ];
    }
}
