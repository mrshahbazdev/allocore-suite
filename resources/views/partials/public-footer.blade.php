@php($brand = config('app.team_branding') ?? [])
@php($social = \App\Models\SiteSetting::value('social_links', []))
@php($footerText = \App\Models\SiteSetting::value('footer_text', ''))
<footer class="border-t border-slate-200 bg-white py-10">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="flex items-center gap-3">
                @if ($brand['logo'] ?? false)
                    <img src="{{ $brand['logo'] }}" alt="" class="h-9 w-9 object-contain rounded-lg">
                @else
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg text-base font-black text-white" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">A</div>
                @endif
                <span class="text-sm font-semibold text-slate-900">{{ $brand['name'] ?? config('app.name') }}</span>
            </div>

            <p class="text-xs text-slate-500">
                {{ filled($footerText) ? $footerText : '&copy; '.date('Y').' '.($brand['name'] ?? config('app.name')).'. '.__('landing.footer.copyright') }}
            </p>

            @if ($social)
                <div class="flex items-center gap-3">
                    @foreach ($social as $link)
                        <a href="{{ $link['url'] ?? '#' }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">{{ $link['label'] ?? '' }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</footer>
