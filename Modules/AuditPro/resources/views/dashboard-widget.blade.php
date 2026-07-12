@php
    $latestAudit = \Modules\AuditPro\Models\Audit::with('results')
        ->where('status', 'completed')
        ->latest('updated_at')
        ->first();
    $activeAudits = \Modules\AuditPro\Models\Audit::where('status', 'in_progress')->count();
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro') }}</p>
            <h3 class="mt-1 font-semibold text-slate-900">{{ __('Maturity snapshot') }}</h3>
        </div>
        <a href="{{ route('audit.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Open') }}</a>
    </div>

    @if ($latestAudit)
        <div class="mt-5 flex items-center gap-5">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-8 border-indigo-100 text-xl font-bold text-indigo-700">
                {{ number_format((float) $latestAudit->results->avg('average_score'), 1) }}
            </div>
            <div class="min-w-0 flex-1 space-y-2">
                @foreach ($latestAudit->results->sortByDesc('average_score')->take(3) as $result)
                    <div>
                        <div class="flex justify-between text-xs text-slate-500"><span>{{ $result->level }}</span><span>{{ number_format((float) $result->average_score, 1) }}</span></div>
                        <div class="mt-1 h-1.5 rounded-full bg-slate-100"><div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ min(100, ((float) $result->average_score / 5) * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="mt-5 text-sm text-slate-500">{{ __('Complete an audit to see maturity analytics.') }}</p>
    @endif

    <p class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500">{{ trans_choice(':count active audit|:count active audits', $activeAudits, ['count' => $activeAudits]) }}</p>
</div>
