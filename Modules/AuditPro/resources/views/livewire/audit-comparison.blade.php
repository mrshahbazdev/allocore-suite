<div>
    @include('auditpro::partials.nav')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Compare audits') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Track maturity changes between two completed assessments.') }}</p>
    </div>

    @if ($availableAudits->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">{{ __('Complete at least one audit to compare results.') }}</div>
    @else
        <div class="mb-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('First audit') }}</label>
                <select wire:model.live="firstAuditId" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">{{ __('Choose an audit') }}</option>
                    @foreach ($availableAudits as $audit)<option value="{{ $audit->id }}">{{ $audit->created_at->format('M d, Y') }} — {{ $audit->template?->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('Second audit') }}</label>
                <select wire:model.live="secondAuditId" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">{{ __('Choose an audit') }}</option>
                    @foreach ($availableAudits as $audit)<option value="{{ $audit->id }}">{{ $audit->created_at->format('M d, Y') }} — {{ $audit->template?->name }}</option>@endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ([__('First audit') => $first, __('Second audit') => $second] as $heading => $data)
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold text-slate-900">{{ $heading }}</h2>
                    @if ($data)
                        <div class="mt-4 flex items-end justify-between border-b border-slate-100 pb-4">
                            <div>
                                <p class="text-sm text-slate-500">{{ $data['audit']->created_at->format('F d, Y') }}</p>
                                <p class="text-sm font-medium text-slate-700">{{ $data['audit']->template?->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-indigo-700">{{ number_format($data['score'], 1) }}</p>
                                <p class="text-xs font-medium text-slate-500">{{ $data['maturity'] }}</p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-4">
                            @foreach ($data['results'] as $result)
                                <div>
                                    <div class="flex justify-between text-sm"><span class="font-medium text-slate-700">{{ $result->level }}</span><span class="text-slate-500">{{ number_format((float) $result->average_score, 1) }}/5</span></div>
                                    <div class="mt-1 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ min(100, ((float) $result->average_score / 5) * 100) }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-6 text-sm text-slate-500">{{ __('Choose an audit above.') }}</p>
                    @endif
                </section>
            @endforeach
        </div>

        @if ($first && $second)
            <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">{{ __('Score change') }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($first['results'] as $level => $result)
                        @php($secondResult = $second['results']->get($level))
                        @if ($secondResult)
                            @php($change = (float) $secondResult->average_score - (float) $result->average_score)
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm">
                                <span class="font-medium text-slate-700">{{ $level }}</span>
                                <span class="{{ $change > 0 ? 'text-emerald-600' : ($change < 0 ? 'text-rose-600' : 'text-slate-500') }}">{{ $change > 0 ? '+' : '' }}{{ number_format($change, 1) }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
