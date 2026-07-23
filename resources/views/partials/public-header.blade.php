<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8" aria-label="Global">
        <a href="/" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-black text-white">A</div>
            <span class="text-lg font-bold text-slate-900">{{ \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')) }}</span>
        </a>

        <div class="hidden items-center gap-8 lg:flex">
            <a href="{{ route('blog.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('Blog') }}</a>
            <a href="{{ route('billing.plans') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('public.nav.pricing') }}</a>
        </div>

        <div class="hidden items-center gap-4 lg:flex">
            @include('partials.locale-switcher')

            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('landing.nav.login') }}</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('public.nav.get_started') }}</a>
            @endif
        </div>

        <div class="flex items-center gap-2 lg:hidden">
            @include('partials.locale-switcher')
        </div>
    </nav>
</header>
