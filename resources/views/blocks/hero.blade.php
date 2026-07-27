@php($brand = config('app.team_branding') ?? [])
<section class="relative overflow-hidden bg-slate-900 py-20 lg:py-32">
    @if ($block['image'] ?? false)
        <img src="{{ $block['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
    @endif
    <div class="relative mx-auto max-w-5xl px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-6xl">{{ $block['heading'] ?? '' }}</h1>
        <p class="mt-6 text-lg leading-8 text-slate-300">{{ $block['subheading'] ?? '' }}</p>
        @if (filled($block['cta_url'] ?? ''))
            <div class="mt-10">
                <a href="{{ $block['cta_url'] }}" class="rounded-lg px-6 py-3 text-sm font-semibold text-white shadow-sm" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">{{ $block['cta_text'] ?? __('Get started') }}</a>
            </div>
        @endif
    </div>
</section>
