@php($s = \App\Support\LandingBlock::settings($block))
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-5xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if ($block['src'] ?? false)
            <img src="{{ $block['src'] }}" alt="{{ $block['alt'] ?? '' }}" class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : '' }}shadow-lg">
        @endif
    </div>
</section>
