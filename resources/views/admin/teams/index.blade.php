@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Teams') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage teams, owners, and subscriptions.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.teams.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by team or owner...') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Owner') }}</th>
                    <th class="px-4 py-3">{{ __('Members') }}</th>
                    <th class="px-4 py-3">{{ __('Subscriptions') }}</th>
                    <th class="px-4 py-3">{{ __('Created') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($teams as $team)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $team->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $team->owner?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $team->members_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $team->tool_subscriptions_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $team->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.teams.show', $team) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Manage') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('No teams found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $teams->links() }}</div>
@endsection
