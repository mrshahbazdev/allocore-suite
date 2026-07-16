@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to users') }}</a>
            @if ($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('Delete this user?') }}')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete user') }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Profile') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Name') }}</dt><dd class="font-medium text-slate-900">{{ $user->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Email') }}</dt><dd class="font-medium text-slate-900">{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Email verified') }}</dt><dd class="font-medium text-slate-900">{{ $user->email_verified_at?->format('d.m.Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Current team') }}</dt><dd class="font-medium text-slate-900">{{ $user->currentTeam?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Roles') }}</dt><dd class="font-medium text-slate-900">{{ $user->getRoleNames()->implode(', ') ?: 'member' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Registered') }}</dt><dd class="font-medium text-slate-900">{{ $user->created_at->format('d.m.Y') }}</dd></div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Teams') }} ({{ $user->teams->count() }})</h2>
            <div class="space-y-3">
                @forelse ($user->teams as $team)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm">
                        <div>
                            <div class="font-medium text-slate-900">{{ $team->name }}</div>
                            <div class="text-slate-500 text-xs capitalize">{{ $team->pivot->role ?? 'member' }}</div>
                        </div>
                        <a href="{{ route('admin.teams.show', $team) }}" class="text-indigo-600 hover:underline text-xs">{{ __('Manage') }}</a>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No teams.') }}</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Subscriptions') }} ({{ $user->toolSubscriptions->count() }})</h2>
            <div class="space-y-3">
                @forelse ($user->toolSubscriptions as $subscription)
                    <div class="rounded-lg bg-slate-50 p-3 text-sm">
                        <div class="font-medium text-slate-900">{{ $subscription->plan?->name ?? '—' }}</div>
                        <div class="text-slate-600 text-xs">{{ $subscription->plan?->modules->pluck('name')->implode(', ') }}</div>
                        <div class="text-slate-500 text-xs mt-1">{{ $subscription->status }} · {{ $subscription->ends_at?->format('d.m.Y') ?? '—' }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No subscriptions.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
