@php($s = \App\Support\LandingBlock::settings($block))
@php($brand = config('app.team_branding') ?? [])
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-4xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if ($s['rounded_class'])
            <div class="{{ $s['rounded_class'] }} {{ $s['border_class'] ? $s['border_class'] : 'border border-white/10' }} bg-white/5 p-8 backdrop-blur lg:p-12">
        @endif
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        @if (filled($block['text'] ?? ''))
            <p class="mt-4 text-lg opacity-90">{{ $block['text'] }}</p>
        @endif
        @if (filled($block['button_url'] ?? ''))
            <div class="mt-8">
                <a href="{{ $block['button_url'] }}" class="rounded-lg bg-white px-6 py-3 text-sm font-semibold" style="color: {{ $brand['primary_color'] ?? '#4f46e5' }}">{{ $block['button_text'] ?? __('Learn more') }}</a>
            </div>
        @endif
        @if ($s['rounded_class'])
            </div>
        @endif
    </div>
</section>
