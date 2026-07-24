<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Active tools') }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active_modules'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Locked add-ons') }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['locked_modules'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Workspace members') }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['workspace_members'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recent activity') }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['recent_activities'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Current plan') }}</p>
        <p class="mt-2 text-lg font-bold text-slate-900 truncate">{{ $subscription?->plan?->name ?? __('Free') }}</p>
    </div>
</div>
