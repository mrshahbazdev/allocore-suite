@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.analytics.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.analytics.description') }}</p>
        </div>
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex items-center gap-2">
            @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => '12 months'] as $days => $label)
                <a href="{{ route('admin.analytics.index', ['period' => $days]) }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $period == $days ? 'bg-indigo-600 text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">{{ __($label) }}</a>
            @endforeach
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        @foreach ([
            ['label' => __('admin.analytics.users_new'), 'value' => $stats['users_new'], 'total' => $stats['users_total']],
            ['label' => __('admin.analytics.teams_new'), 'value' => $stats['teams_new'], 'total' => $stats['teams_total']],
            ['label' => __('admin.analytics.subscriptions_new'), 'value' => $stats['subscriptions_new'], 'total' => $stats['active_subscriptions']],
            ['label' => __('admin.analytics.revenue_new'), 'value' => '$'.number_format($stats['revenue_new'], 2), 'total' => '$'.number_format($stats['revenue_total'], 2)],
        ] as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $stat['value'] }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ __('admin.analytics.total') }}: {{ $stat['total'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mb-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('admin.analytics.user_signups') }}</h2>
            @if ($userSignups->isEmpty())
                <p class="text-sm text-slate-500">{{ __('admin.analytics.no_data') }}</p>
            @else
                <div class="space-y-2">
                    @foreach ($userSignups as $date => $total)
                        <div class="flex items-center gap-3">
                            <div class="w-24 text-xs text-slate-500">{{ $date }}</div>
                            <div class="flex-1">
                                <div class="h-4 rounded bg-indigo-100" style="width: {{ min(100, $total * 10) }}%"></div>
                            </div>
                            <div class="w-8 text-right text-sm font-medium text-slate-900">{{ $total }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('admin.analytics.revenue_by_day') }}</h2>
            @if ($revenueByDay->isEmpty())
                <p class="text-sm text-slate-500">{{ __('admin.analytics.no_data') }}</p>
            @else
                <div class="space-y-2">
                    @foreach ($revenueByDay as $date => $total)
                        <div class="flex items-center gap-3">
                            <div class="w-24 text-xs text-slate-500">{{ $date }}</div>
                            <div class="flex-1">
                                <div class="h-4 rounded bg-emerald-100" style="width: {{ min(100, $total > 0 ? ($total / max($revenueByDay->max(), 1) * 100) : 0) }}%"></div>
                            </div>
                            <div class="w-16 text-right text-sm font-medium text-slate-900">${{ number_format($total, 0) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('admin.analytics.top_teams') }}</h2>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Users') }}</th>
                    <th class="px-4 py-3">{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($topTeams as $team)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $team->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $team->members_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $team->created_at->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">{{ __('No teams yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
