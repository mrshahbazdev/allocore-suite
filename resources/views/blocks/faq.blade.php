@php($s = \App\Support\LandingBlock::settings($block))
<section class="{{ $s['padding_class'] }} {{ $s['animation_class'] }}" {!! $s['inline_style'] ? 'style="'.$s['inline_style'].'"' : '' !!}>
    <div class="{{ $s['container_class'] ?: 'mx-auto max-w-3xl px-6 lg:px-8' }} {{ $s['text_align_class'] }}">
        @if (filled($block['title'] ?? ''))
            <h2 class="text-3xl font-bold tracking-tight">{{ $block['title'] }}</h2>
        @endif
        <div class="mt-10 space-y-4 {{ $s['text_align_class'] === 'text-left' ? '' : 'text-left' }}">
            @foreach ($block['items'] ?? [] as $item)
                <details class="group {{ $s['rounded_class'] ? $s['rounded_class'].' ' : '' }}border border-slate-200 bg-white p-4">
                    <summary class="flex cursor-pointer items-center justify-between text-left font-semibold list-none">
                        {{ $item['question'] ?? '' }}
                        <span class="ml-2 text-slate-400 group-open:hidden">+</span>
                        <span class="ml-2 text-slate-400 hidden group-open:inline">-</span>
                    </summary>
                    <div class="mt-2 text-sm opacity-80">{{ $item['answer'] ?? '' }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
