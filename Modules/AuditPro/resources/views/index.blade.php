@extends('layouts.shell')

@section('content')
    @include('auditpro::partials.nav')

    {{-- Header --}}
    <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-[#ff9200]">{{ __('AuditPro') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Business maturity overview') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ __('Assess :team across five operational pillars.', ['team' => auth()->user()->currentTeam->name]) }}</p>
            </div>
            <a href="#audit-form" class="inline-flex items-center rounded-lg bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                {{ __('Start audit') }}
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php($statCards = [
        ['label' => __('Total audits'), 'value' => $stats['total'], 'color' => 'bg-[#ff9200]', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['label' => __('In progress'), 'value' => $stats['active'], 'color' => 'bg-[#0094af]', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => __('Completed'), 'value' => $stats['completed'], 'color' => 'bg-emerald-500', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => __('Average score'), 'value' => number_format($stats['average'], 1).'/4', 'color' => 'bg-slate-700', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
    ])
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statCards as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $card['value'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $card['color'] }} text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- New audit form --}}
    <div id="audit-form" class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Configure & start audit') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Fill in the details below to start a new audit.') }}</p>
        </div>
        <form method="POST" action="{{ route('audit.start') }}">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Audit type') }}</label>
                    <select name="audit_type" required class="w-full rounded-lg border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]/20">
                        <option value="major">{{ __('Major audit') }} — {{ __('every 6 months') }}</option>
                        <option value="small">{{ __('Small audit') }} — {{ __('every 3 months') }}</option>
                        <option value="challenge">{{ __('Challenge') }} — {{ __('every 4 weeks') }}</option>
                        <option value="kpi_check">{{ __('KPI check') }} — {{ __('every week') }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Template') }}</label>
                    <select name="template_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]/20">
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->questions_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Company name') }}</label>
                    <input type="text" name="company_name" value="{{ old('company_name', auth()->user()->currentTeam->company_name ?? auth()->user()->currentTeam->name) }}" placeholder="{{ __('Company name') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]/20" required>
                </div>
                <div class="sm:col-span-2 lg:col-span-1 xl:col-span-2">
                    @include('partials.industry-select', ['clusters' => $industryClusters, 'selected' => ['industry' => old('industry', auth()->user()->currentTeam->industry), 'industry_sub' => old('industry_sub', auth()->user()->currentTeam->industry_sub)], 'value' => auth()->user()->currentTeam])
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Company size') }}</label>
                    <select name="size" required class="w-full rounded-lg border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]/20">
                        <option value="">{{ __('Company size') }}</option>
                        <option value="1-10" {{ old('size', auth()->user()->currentTeam->size) === '1-10' ? 'selected' : '' }}>1–10</option>
                        <option value="11-50" {{ old('size', auth()->user()->currentTeam->size) === '11-50' ? 'selected' : '' }}>11–50</option>
                        <option value="51-200" {{ old('size', auth()->user()->currentTeam->size) === '51-200' ? 'selected' : '' }}>51–200</option>
                        <option value="201-500" {{ old('size', auth()->user()->currentTeam->size) === '201-500' ? 'selected' : '' }}>201–500</option>
                        <option value="501+" {{ old('size', auth()->user()->currentTeam->size) === '501+' ? 'selected' : '' }}>501+</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Age (years)') }}</label>
                    <input type="number" name="company_age" value="{{ old('company_age', auth()->user()->currentTeam->company_age) }}" min="0" max="250" placeholder="{{ __('Age (years)') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]/20" required>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button class="inline-flex items-center rounded-lg bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    {{ __('Start audit') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Recent audits --}}
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-900">{{ __('Recent audits') }}</h2>
                <p class="text-xs text-slate-500">{{ __('Latest assessments for the current team.') }}</p>
            </div>
            <a href="{{ route('audit.audits') }}" class="text-sm font-medium text-[#0094af] hover:text-[#007d93]">{{ __('View all') }}</a>
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
                            <td class="px-5 py-4 text-slate-600">{{ $audit->status === 'completed' ? number_format((float) $audit->results->avg('average_score'), 1).'/4' : '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ $audit->status === 'completed' ? route('audit.results', $audit) : route('audit.assessment', $audit) }}" class="rounded-lg bg-[#ff9200] px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                    {{ $audit->status === 'completed' ? __('View') : __('Resume') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="text-sm text-slate-500">{{ __('No audits yet. Start the first assessment above.') }}</p>
                                <a href="#audit-form" class="mt-3 inline-flex items-center rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">{{ __('Start audit') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
