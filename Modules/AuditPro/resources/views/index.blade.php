@extends('layouts.shell')

@section('content')
    @include('auditpro::partials.nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Business maturity overview') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Assess :team across five operational pillars.', ['team' => auth()->user()->currentTeam->name]) }}</p>
        </div>
        <form method="POST" action="{{ route('audit.start') }}" class="flex gap-2">
            @csrf
            <select name="template_id" required class="rounded-lg border-slate-300 text-sm">
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->questions_count }})</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ __('Start audit') }}
            </button>
        </form>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            __('Total audits') => $stats['total'],
            __('In progress') => $stats['active'],
            __('Completed') => $stats['completed'],
            __('Average score') => number_format($stats['average'], 1).'/5',
        ] as $label => $value)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-900">{{ __('Recent audits') }}</h2>
                <p class="text-xs text-slate-500">{{ __('Latest assessments for the current team.') }}</p>
            </div>
            <a href="{{ route('audit.audits') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View all') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('Template') }}</th>
                        <th class="px-5 py-3">{{ __('Owner') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Score') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($audits as $audit)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $audit->template?->name ?? __('Archived template') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $audit->creator?->name ?? __('Deleted user') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $audit->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $audit->status === 'completed' ? __('Completed') : __('In progress') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $audit->status === 'completed' ? number_format((float) $audit->results->avg('average_score'), 1).'/5' : '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ $audit->status === 'completed' ? route('audit.results', $audit) : route('audit.assessment', $audit) }}" class="font-medium text-indigo-600 hover:underline">
                                    {{ $audit->status === 'completed' ? __('View') : __('Resume') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">{{ __('No audits yet. Start the first assessment above.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
