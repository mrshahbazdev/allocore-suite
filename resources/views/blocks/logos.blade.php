@php($s = \App\Support\LandingBlock::settings($block))
@php($gridCols = filled($s['columns_class']) ? $s['columns_class'] : 'sm:grid-cols-3 lg:grid-cols-5')
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid {{ $gridCols }} {{ $s['gap_class'] }} {{ $s['align_class'] }} place-items-center opacity-70 grayscale transition hover:grayscale-0">
            @foreach ($block['items'] ?? [] as $item)
                <div class="flex h-16 items-center justify-center px-4">
                    @if (filled($item['image_url'] ?? ''))
                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] ?? '' }}" class="max-h-10 w-auto">
                    @else
                        <span class="text-sm font-semibold">{{ $item['name'] ?? '' }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
