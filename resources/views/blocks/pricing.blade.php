@php($s = \App\Support\LandingBlock::settings($block))
@php($brand = config('app.team_branding') ?? [])
@php($gridCols = filled($s['columns_class']) ? $s['columns_class'] : 'sm:grid-cols-2 lg:grid-cols-3')
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid {{ $gridCols }} {{ $s['gap_class'] }} {{ $s['align_class'] }} {{ $s['text_align_class'] === 'text-left' ? '' : 'text-left' }}">
            @foreach ($block['items'] ?? [] as $item)
                @php($features = array_filter(preg_split('/\r\n|\r|\n|,/', $item['features'] ?? '')))
                @php($highlighted = filter_var($item['highlighted'] ?? false, FILTER_VALIDATE_BOOLEAN))
                <div class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : '' }}{{ $s['border_class'] || ! $highlighted ? ($s['border_class'] ? $s['border_class'].' ' : 'border border-slate-200 ') : '' }}{{ $highlighted ? 'ring-2 ring-indigo-600 ' : '' }}bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold">{{ $item['name'] ?? '' }}</h3>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-bold tracking-tight">{{ $item['price'] ?? '' }}</span>
                        @if (filled($item['period'] ?? ''))
                            <span class="text-sm opacity-70">/{{ $item['period'] }}</span>
                        @endif
                    </div>
                    @if ($features)
                        <ul class="mt-6 space-y-2 text-sm opacity-80">
                            @foreach ($features as $feature)
                                <li class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    {{ trim($feature) }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if (filled($item['cta_url'] ?? ''))
                        <a href="{{ $item['cta_url'] }}" class="mt-6 inline-block w-full rounded-lg px-4 py-2 text-center text-sm font-semibold text-white" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">{{ $item['cta_text'] ?? __('Choose plan') }}</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
