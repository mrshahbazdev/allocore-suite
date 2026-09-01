@php($s = \App\Support\LandingBlock::settings($block))
@php($brand = config('app.team_branding') ?? [])
@php($gridCols = filled($s['columns_class']) ? $s['columns_class'] : 'sm:grid-cols-2 lg:grid-cols-3')
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight text-inherit">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid {{ $gridCols }} {{ $s['gap_class'] }} {{ $s['align_class'] }} {{ $s['text_align_class'] === 'text-left' ? '' : 'text-left' }}">
            @foreach ($block['items'] ?? [] as $item)
                <div class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : 'rounded-2xl ' }}{{ $s['border_class'] ? $s['border_class'].' ' : 'border border-slate-200/80 ' }}bg-white p-6 shadow-sm text-slate-800">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg text-white shadow-sm" style="background-color: {{ $brand['primary_color'] ?? '#ff9200' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
