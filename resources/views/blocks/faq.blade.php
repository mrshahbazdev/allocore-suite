<section class="py-16 lg:py-24 bg-slate-50">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-center text-3xl font-bold tracking-tight text-slate-900">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 space-y-4">
            @foreach ($block['items'] ?? [] as $item)
                <details class="group rounded-xl border border-slate-200 bg-white p-4">
                    <summary class="flex cursor-pointer items-center justify-between text-left font-semibold text-slate-900 list-none">
                        {{ $item['question'] ?? '' }}
                        <span class="ml-2 text-slate-400 group-open:hidden">+</span>
                        <span class="ml-2 text-slate-400 hidden group-open:inline">-</span>
                    </summary>
                    <div class="mt-2 text-sm text-slate-600">{{ $item['answer'] ?? '' }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
