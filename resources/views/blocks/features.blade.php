@php($brand = config('app.team_branding') ?? [])
<section class="py-16 lg:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-center text-3xl font-bold tracking-tight text-slate-900">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($block['items'] ?? [] as $item)
                <div class="rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg text-white" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
