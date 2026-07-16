@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $audit->name ?? $audit->template?->name ?? __('Audit') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Team') }}: {{ $audit->team?->name ?? '—' }} · {{ __('Created by') }} {{ $audit->creator?->name ?? '—' }}</p>
        </div>
        <a href="{{ route('admin.audits.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to audits') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Details') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium text-slate-900 capitalize">{{ $audit->status }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Total score') }}</dt><dd class="font-medium text-slate-900">{{ $audit->total_score !== null ? number_format($audit->total_score, 1) : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Created') }}</dt><dd class="font-medium text-slate-900">{{ $audit->created_at->format('d.m.Y H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Updated') }}</dt><dd class="font-medium text-slate-900">{{ $audit->updated_at->format('d.m.Y H:i') }}</dd></div>
            </dl>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Results by pillar') }}</h2>
                @if ($audit->results->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($audit->results as $result)
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm">
                                <div class="font-medium text-slate-900">{{ $result->pillar?->name ?? '—' }}</div>
                                <div class="text-right">
                                    <div class="font-medium text-slate-900">{{ number_format($result->average_score, 1) }} / {{ number_format($result->total_points, 1) }}</div>
                                    <div class="text-xs text-slate-500 capitalize">{{ $result->maturity_level ?? $result->level }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-slate-500">{{ __('No results yet.') }}</div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Answers') }} ({{ $audit->answers->count() }})</h2>
                @if ($audit->answers->isNotEmpty())
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('Question') }}</th>
                                <th class="px-4 py-3">{{ __('Answer') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($audit->answers as $answer)
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">{{ $answer->question?->question ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ is_array($answer->value) ? json_encode($answer->value, JSON_UNESCAPED_UNICODE) : ($answer->value ?? '—') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-sm text-slate-500">{{ __('No answers yet.') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
