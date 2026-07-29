@php($s = \App\Support\LandingBlock::settings($block))
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-3xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        <div class="prose prose-slate max-w-none {{ $s['rounded_class'] ? $s['rounded_class'].' p-6 ' : '' }}{{ $s['border_class'] ? $s['border_class'].' ' : '' }}">
            {!! $block['content'] ?? '' !!}
        </div>
    </div>
</section>
