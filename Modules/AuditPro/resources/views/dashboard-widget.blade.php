<div class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
    <div class="border-b border-slate-200 p-5 sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro') }}</p>
                <h3 class="mt-1 text-xl font-bold text-slate-900">{{ __('Corporate Maturity') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('A weighted view of the five Business Readiness phases.') }}</p>
            </div>
            <a href="{{ route('audit.index') }}" class="inline-flex w-fit items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ __('Open AuditPro') }}
            </a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-indigo-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Organizational Maturity') }}</p>
                <p class="mt-2 text-3xl font-bold text-indigo-950">
                    {{ $overallPercent === null ? '—' : number_format($overallPercent, 1).'%' }}
                </p>
                <p class="mt-1 text-xs text-indigo-700">
                    {{ $overallScore === null ? __('Complete an audit to calculate it.') : number_format($overallScore, 1).'/5 '.__('overall score') }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Business Readiness target') }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">100%</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('5 phases × 20%') }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Active audits') }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $activeAudits }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $latestAudit ? __('Latest result :date', ['date' => $latestAudit->updated_at->format('M j, Y')]) : __('No completed audit yet') }}</p>
            </div>
        </div>
    </div>

    @if ($phases->isEmpty())
        <p class="p-6 text-sm text-slate-500">{{ __('Select a team to view its Business Readiness framework.') }}</p>
    @else
        <div class="border-b border-slate-200 p-5 sm:p-6">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Quick overview') }}</p>
            <details open class="group overflow-hidden rounded-xl border border-slate-200">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-slate-50 px-4 py-3">
                    <span>
                        <span class="block font-semibold text-slate-900">{{ __('Business Readiness') }}</span>
                        <span class="text-xs text-slate-500">{{ __('Corporate Maturity basic KPI') }}</span>
                    </span>
                    <span class="flex items-center gap-3 text-sm">
                        <span class="font-medium text-slate-500">{{ __('100% target') }}</span>
                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 font-semibold text-indigo-700">
                            {{ $overallPercent === null ? __('Not measured') : number_format($overallPercent, 1).'%' }}
                        </span>
                    </span>
                </summary>

                <div class="space-y-2 p-3">
                    @foreach ($phases as $phase)
                        <details class="overflow-hidden rounded-lg border border-slate-200">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50">
                                <span class="font-medium text-slate-900">{{ $phase['name'] }}</span>
                                <span class="flex items-center gap-3 text-xs">
                                    <span class="text-slate-500">{{ number_format($phase['target']) }}% {{ __('target') }}</span>
                                    <span class="min-w-20 text-right font-semibold text-indigo-700">
                                        {{ $phase['contribution'] === null ? '—' : number_format($phase['contribution'], 1).'%' }}
                                    </span>
                                </span>
                            </summary>
                            <ul class="divide-y divide-slate-100 border-t border-slate-100 bg-slate-50/60">
                                @foreach ($phase['questions'] as $question)
                                    <li class="flex items-start justify-between gap-4 px-4 py-2.5 text-sm">
                                        <span class="text-slate-700">{{ $question['position'] }}. {{ $question['name'] }}</span>
                                        <span class="shrink-0 font-medium text-slate-500">{{ number_format($question['target']) }}% {{ __('target') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endforeach
                </div>
            </details>
        </div>

        <div class="p-5 sm:p-6">
            <div class="mb-4">
                <h4 class="font-semibold text-slate-900">{{ __('Phase KPI details') }}</h4>
                <p class="text-sm text-slate-500">{{ __('Scores and weighted contribution from the latest completed AuditPro assessment.') }}</p>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($phases as $phase)
                    <section class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Phase :phase', ['phase' => $loop->iteration]) }}</p>
                                <h5 class="mt-1 text-lg font-semibold text-slate-900">{{ $phase['name'] }}</h5>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-slate-900">{{ $phase['contribution'] === null ? '—' : number_format($phase['contribution'], 1).'%' }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($phase['target']) }}% {{ __('target') }}</p>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $phase['description'] }}</p>

                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ $phase['contribution'] === null ? 0 : min(100, ($phase['contribution'] / $phase['target']) * 100) }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-xs text-slate-500">
                            {{ $phase['score'] === null ? __('Not measured') : number_format($phase['score'], 1).'/5' }}
                        </p>

                        <div class="mt-4 divide-y divide-slate-100 border-t border-slate-100">
                            @foreach ($phase['questions'] as $question)
                                <div class="py-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <p class="text-sm font-medium text-slate-800">{{ $question['position'] }}. {{ $question['name'] }}</p>
                                        <div class="shrink-0 text-right">
                                            <p class="text-sm font-semibold text-slate-900">{{ $question['contribution'] === null ? '—' : number_format($question['contribution'], 1).'%' }}</p>
                                            <p class="text-[11px] text-slate-500">{{ number_format($question['target']) }}% {{ __('target') }}</p>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $question['description'] }}</p>
                                    @if ($question['score'] !== null)
                                        <p class="mt-1 text-xs font-medium text-indigo-600">{{ number_format($question['score'], 1) }}/5</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    @endif
</div>
