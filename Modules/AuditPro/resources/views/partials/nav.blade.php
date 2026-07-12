<nav class="mb-6 flex flex-wrap gap-2">
    @foreach ([
        'audit.index' => __('Overview'),
        'audit.audits' => __('Audits'),
        'audit.templates' => __('Templates'),
        'audit.compare' => __('Compare'),
    ] as $routeName => $label)
        <a href="{{ route($routeName) }}"
           class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($routeName) ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-300 hover:text-indigo-600' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
