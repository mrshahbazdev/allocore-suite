@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Financial') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Analyses and KPI snapshots across all teams.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        @foreach ([
            ['label' => __('Total analyses'), 'value' => $summary['total']],
            ['label' => __('Complete'), 'value' => $summary['complete']],
            ['label' => __('Average score'), 'value' => number_format($summary['average_score'], 1)],
        ] as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.financial.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, company, or team...') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Analysis') }}</th>
                    <th class="px-4 py-3">{{ __('Company') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Score') }}</th>
                    <th class="px-4 py-3">{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($analyses as $analysis)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $analysis->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $analysis->company?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $analysis->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $analysis->typeLabel() }}</td>
                        <td class="px-4 py-3 text-slate-600 capitalize">{{ $analysis->status }}</td>
                        <td class="px-4 py-3">
                            @if ($analysis->total_score !== null)
                                @php $color = $analysis->scoreColor(); @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $color === 'green' ? 'bg-emerald-100 text-emerald-800' : ($color === 'yellow' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ number_format($analysis->total_score, 1) }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $analysis->created_at->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">{{ __('No analyses found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $analyses->links() }}</div>
@endsection
