@extends('layouts.shell')

@section('content')
    @include('auditpro::partials.nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Business maturity overview') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Assess :team across five operational pillars.', ['team' => auth()->user()->currentTeam->name]) }}</p>
        </div>
        <form method="POST" action="{{ route('audit.start') }}" class="grid gap-3 sm:flex sm:flex-wrap sm:items-end">
            @csrf
            <div class="grid gap-3 sm:flex sm:flex-wrap">
                <select name="audit_type" required class="rounded-lg border-slate-300 text-sm">
                    <option value="major">{{ __('Major audit') }} — {{ __('every 6 months') }}</option>
                    <option value="small">{{ __('Small audit') }} — {{ __('every 3 months') }}</option>
                    <option value="challenge">{{ __('Challenge') }} — {{ __('every 4 weeks') }}</option>
                    <option value="kpi_check">{{ __('KPI check') }} — {{ __('every week') }}</option>
                </select>
                <select name="template_id" required class="rounded-lg border-slate-300 text-sm">
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->questions_count }})</option>
                    @endforeach
                </select>
                <input type="text" name="company_name" value="{{ old('company_name', auth()->user()->currentTeam->company_name ?? auth()->user()->currentTeam->name) }}" placeholder="{{ __('Company name') }}" class="rounded-lg border-slate-300 text-sm" required>
                @include('partials.industry-select', ['clusters' => $industryClusters, 'selected' => ['industry' => old('industry', auth()->user()->currentTeam->industry), 'industry_sub' => old('industry_sub', auth()->user()->currentTeam->industry_sub)], 'value' => auth()->user()->currentTeam]
                <select name="size" required class="rounded-lg border-slate-300 text-sm">
                    <option value="">{{ __('Company size') }}</option>
                    <option value="1-10" {{ old('size', auth()->user()->currentTeam->size) === '1-10' ? 'selected' : '' }}>1–10</option>
                    <option value="11-50" {{ old('size', auth()->user()->currentTeam->size) === '11-50' ? 'selected' : '' }}>11–50</option>
                    <option value="51-200" {{ old('size', auth()->user()->currentTeam->size) === '51-200' ? 'selected' : '' }}>51–200</option>
                    <option value="201-500" {{ old('size', auth()->user()->currentTeam->size) === '201-500' ? 'selected' : '' }}>201–500</option>
                    <option value="501+" {{ old('size', auth()->user()->currentTeam->size) === '501+' ? 'selected' : '' }}>501+</option>
                </select>
                <input type="number" name="company_age" value="{{ old('company_age', auth()->user()->currentTeam->company_age) }}" min="0" max="250" placeholder="{{ __('Age (years)') }}" class="rounded-lg border-slate-300 text-sm" required>
            </div>
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
                        <th class="px-5 py-3">{{ __('Type') }}</th>
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
                            <td class="px-5 py-4 text-slate-600">{{ __(ucfirst(str_replace('_', ' ', $audit->audit_type))) }}</td>
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
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">{{ __('No audits yet. Start the first assessment above.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
