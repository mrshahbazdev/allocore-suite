@php($s = \App\Support\LandingBlock::settings($block))
@php($brand = config('app.team_branding') ?? [])
<section class="relative overflow-hidden {{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    @if ($block['image'] ?? false)
        <img src="{{ $block['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
    @endif
    <div class="relative {{ $s['container_class'] ?: 'mx-auto max-w-5xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if ($s['rounded_class'])
            <div class="{{ $s['rounded_class'] }} {{ $s['border_class'] ? $s['border_class'] : 'border border-white/10' }} bg-white/5 p-8 backdrop-blur lg:p-12">
        @endif
        <h1 class="text-4xl font-extrabold tracking-tight sm:text-6xl">{{ $block['heading'] ?? '' }}</h1>
        <p class="mt-6 text-lg leading-8 opacity-90">{{ $block['subheading'] ?? '' }}</p>
        @if (filled($block['cta_url'] ?? ''))
            <div class="mt-10">
                <a href="{{ $block['cta_url'] }}" class="rounded-lg px-6 py-3 text-sm font-semibold text-white shadow-sm" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">{{ $block['cta_text'] ?? __('Get started') }}</a>
            </div>
        @endif
        @if ($s['rounded_class'])
            </div>
        @endif
    </div>
</section>
