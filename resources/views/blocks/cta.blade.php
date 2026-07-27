@php($brand = config('app.team_branding') ?? [])
<section class="py-16 lg:py-24" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">
    <div class="mx-auto max-w-4xl px-6 lg:px-8 text-center">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight text-white">{{ $block['title'] }}</h2>
        @endif
        @if (filled($block['text'] ?? ''))
            <p class="mt-4 text-lg text-white/90">{{ $block['text'] }}</p>
        @endif
        @if (filled($block['button_url'] ?? ''))
            <div class="mt-8">
                <a href="{{ $block['button_url'] }}" class="rounded-lg bg-white px-6 py-3 text-sm font-semibold" style="color: {{ $brand['primary_color'] ?? '#4f46e5' }}">{{ $block['button_text'] ?? __('Learn more') }}</a>
            </div>
        @endif
    </div>
</section>
