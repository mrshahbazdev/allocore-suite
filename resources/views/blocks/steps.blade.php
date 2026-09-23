@php($s = \App\Support\LandingBlock::settings($block))
@php($brand = config('app.team_branding') ?? [])
@php($gridCols = filled($s['columns_class']) ? $s['columns_class'] : 'sm:grid-cols-2 lg:grid-cols-4')
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight text-inherit">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid {{ $gridCols }} {{ $s['gap_class'] }} {{ $s['align_class'] }} {{ $s['text_align_class'] === 'text-left' ? '' : 'text-left' }}">
            @foreach ($block['items'] ?? [] as $index => $item)
                <div class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : 'rounded-2xl ' }}{{ $s['border_class'] ? $s['border_class'].' ' : 'border border-slate-200/80 ' }}relative bg-white p-6 shadow-sm text-slate-800">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white shadow-sm" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">{{ $index + 1 }}</div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
