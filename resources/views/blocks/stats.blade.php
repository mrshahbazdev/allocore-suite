@php($s = \App\Support\LandingBlock::settings($block))
@php($gridCols = filled($s['columns_class']) ? $s['columns_class'] : 'sm:grid-cols-2 lg:grid-cols-4')
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid {{ $gridCols }} {{ $s['gap_class'] }} {{ $s['align_class'] }}">
            @foreach ($block['items'] ?? [] as $item)
                <div class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : '' }}{{ $s['border_class'] ? $s['border_class'].' ' : '' }}bg-white p-6 shadow-sm">
                    <div class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $item['value'] ?? '0' }}{{ $item['suffix'] ?? '' }}</div>
                    <div class="mt-2 text-sm font-medium text-slate-600">{{ $item['label'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
