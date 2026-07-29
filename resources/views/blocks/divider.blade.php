@php($s = \App\Support\LandingBlock::settings($block))
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @php($color = filled($block['color'] ?? '') ? 'background-color: '.e($block['color']).';' : 'background-color: #e2e8f0;')
        @php($width = filled($block['width'] ?? '') ? 'width: '.e($block['width']).';' : 'width: 100%;')
        <div class="mx-auto h-px" style="{{ $color }}{{ $width }}"></div>
        @if (filled($block['icon'] ?? ''))
            <div class="mt-4">
                <img src="{{ $block['icon'] }}" alt="" class="mx-auto h-8 w-8 opacity-50">
            </div>
        @endif
    </div>
</section>
