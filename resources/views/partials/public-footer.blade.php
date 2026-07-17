<footer class="border-t border-slate-200 bg-white py-10">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-base font-black text-white">A</div>
                <span class="text-sm font-semibold text-slate-900">{{ \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')) }}</span>
            </div>
            <p class="text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')) }}. {{ __('landing.footer.copyright') }}
            </p>
        </div>
    </div>
</footer>
