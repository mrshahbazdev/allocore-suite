@php($s = \App\Support\LandingBlock::settings($block))
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }} {{ $s['is_dark_bg'] ? 'is-dark-section' : '' }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-4xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        <div class="prose max-w-none [color:inherit] [&_h1]:[color:inherit] [&_h2]:[color:inherit] [&_h3]:[color:inherit] [&_h4]:[color:inherit] [&_p]:[color:inherit] [&_strong]:[color:inherit] {{ $s['rounded_class'] ? $s['rounded_class'].' p-6 ' : '' }}{{ $s['border_class'] ? $s['border_class'].' ' : '' }}">
            {!! $block['content'] ?? '' !!}
        </div>
    </div>
</section>
