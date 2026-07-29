@php($s = \App\Support\LandingBlock::settings($block))
@php($gridCols = filled($s['columns_class']) ? $s['columns_class'] : 'sm:grid-cols-2 lg:grid-cols-3')
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid {{ $gridCols }} {{ $s['gap_class'] }} {{ $s['align_class'] }} {{ $s['text_align_class'] === 'text-left' ? '' : 'text-left' }}">
            @foreach ($block['items'] ?? [] as $item)
                <div class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : '' }}{{ $s['border_class'] ? $s['border_class'].' ' : '' }}bg-white p-6 shadow-sm">
                    <p class="text-lg font-medium leading-relaxed opacity-90">“{{ $item['quote'] ?? '' }}”</p>
                    <div class="mt-4 text-sm font-semibold">{{ $item['author'] ?? '' }}</div>
                    <div class="text-xs opacity-70">{{ $item['role'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
