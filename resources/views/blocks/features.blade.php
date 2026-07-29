@php($s = \App\Support\LandingBlock::settings($block))
@php($brand = config('app.team_branding') ?? [])
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-7xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3 {{ $s['text_align_class'] === 'text-left' ? '' : 'text-left' }}">
            @foreach ($block['items'] ?? [] as $item)
                <div class="{{ $s['rounded_class'] ? $s['rounded_class'].' ' : '' }}border border-slate-200 p-6 shadow-sm {{ $s['bg_style'] ? '' : 'bg-white' }}">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg text-white" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm opacity-80">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
