@php($brand = config('app.team_branding') ?? [])
@php($menu = \App\Models\SiteSetting::value('public_nav_menu', []))
<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8" aria-label="Global">
        <a href="/" class="flex items-center gap-3">
            @if ($brand['logo'] ?? false)
                <img src="{{ $brand['logo'] }}" alt="" class="h-10 w-10 object-contain rounded-xl">
            @else
                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-lg font-black text-white" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">A</div>
            @endif
            <span class="text-lg font-bold text-slate-900">{{ $brand['name'] ?? config('app.name') }}</span>
        </a>

        <div class="hidden items-center gap-8 lg:flex">
            @foreach ($menu as $item)
                <a href="{{ $item['url'] ?? '#' }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ $item['label'] ?? '' }}</a>
            @endforeach
            <a href="{{ route('glossary.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('Glossary') }}</a>
            <a href="{{ route('blog.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('Blog') }}</a>
            <a href="{{ route('billing.plans') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('public.nav.pricing') }}</a>
            <a href="{{ route('api-docs.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('API Docs') }}</a>
        </div>

        <div class="hidden items-center gap-4 lg:flex">
            @include('partials.locale-switcher')

            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('landing.nav.login') }}</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-90" style="background-color: {{ $brand['primary_color'] ?? '#4f46e5' }}">{{ __('public.nav.get_started') }}</a>
            @endif
        </div>

        <div class="flex items-center gap-2 lg:hidden">
            @include('partials.locale-switcher')
        </div>
    </nav>
</header>
