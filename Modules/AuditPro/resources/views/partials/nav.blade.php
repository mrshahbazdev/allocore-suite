<nav class="mb-8 flex flex-wrap gap-6 border-b border-slate-200">
    @foreach ([
        'audit.index' => __('Overview'),
        'audit.audits' => __('Audits'),
        'audit.challenges.index' => __('Challenges'),
        'audit.templates' => __('Templates'),
        'audit.compare' => __('Compare'),
    ] as $routeName => $label)
        <a href="{{ route($routeName) }}"
           class="-mb-px border-b-2 pb-3 text-sm font-medium transition-colors {{ request()->routeIs($routeName) ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
