@extends('layouts.shell', ['title' => __('Team Analytics')])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Team Analytics') }}</h1>

    @if (! $team ?? false)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-800">{{ __('Select a team to see analytics.') }}</div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Inbox') }}</div><div class="text-2xl font-bold">{{ $task_distribution['inbox'] }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Keep') }}</div><div class="text-2xl font-bold">{{ $task_distribution['keep'] }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Delegate') }}</div><div class="text-2xl font-bold">{{ $task_distribution['delegate'] }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Drop') }}</div><div class="text-2xl font-bold">{{ $task_distribution['drop'] }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-sm text-slate-500">{{ __('Team Focus Score') }}</div><div class="text-2xl font-bold text-indigo-600">{{ $team_focus_score }}%</div></div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Delegations') }}</h2>
                <ul class="text-sm space-y-1">
                    <li>{{ __('Open') }}: {{ $delegations['open'] }}</li>
                    <li>{{ __('Done') }}: {{ $delegations['done'] }}</li>
                    <li>{{ __('Overdue') }}: {{ $delegations['overdue'] }}</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Category Distribution') }}</h2>
                <ul class="text-sm space-y-1">
                    @foreach ($category_distribution as $cat => $n)
                        <li>{{ __(Modules\FocusMatrix\Models\Task::CATEGORIES[$cat] ?? $cat) }}: {{ $n }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Member Stats') }}</h2>
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500 border-b border-slate-100">
                    <tr><th>{{ __('Name') }}</th><th>{{ __('Tasks') }}</th><th>{{ __('Keep') }}</th><th>{{ __('Delegate') }}</th><th>{{ __('Focus Score') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($member_stats as $m)
                        <tr>
                            <td class="py-2">{{ $m['name'] }}</td>
                            <td>{{ $m['tasks_total'] }}</td>
                            <td>{{ $m['keep'] }}</td>
                            <td>{{ $m['delegate'] }}</td>
                            <td>{{ $m['focus_score'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
