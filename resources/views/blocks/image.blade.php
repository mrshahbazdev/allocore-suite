<section class="py-16 lg:py-24 bg-white">
    <div class="mx-auto max-w-5xl px-6 lg:px-8 text-center">
        @if ($block['src'] ?? false)
            <img src="{{ $block['src'] }}" alt="{{ $block['alt'] ?? '' }}" class="rounded-2xl shadow-lg">
        @endif
    </div>
</section>
