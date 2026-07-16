@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $team->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('Team details, members, and subscriptions.') }}</p>
        </div>
        <a href="{{ route('admin.teams.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to teams') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Team profile') }}</h2>
                <form method="POST" action="{{ route('admin.teams.update', $team) }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Name') }}</label>
                        <input type="text" name="name" value="{{ $team->name }}" class="w-full rounded-lg border-slate-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Owner') }}</label>
                        <input type="text" value="{{ $team->owner?->name ?? '—' }}" class="w-full rounded-lg border-slate-300 bg-slate-50 text-sm" disabled>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Industry') }}</label>
                        <input type="text" name="industry" value="{{ $team->industry }}" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Size') }}</label>
                        <input type="text" name="size" value="{{ $team->size }}" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update team') }}</button>
                        <button form="delete-team" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50" onclick="return confirm('{{ __('Delete this team?') }}')">{{ __('Delete') }}</button>
                    </div>
                </form>
                <form id="delete-team" method="POST" action="{{ route('admin.teams.destroy', $team) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Members') }} ({{ $team->members->count() }})</h2>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Email') }}</th>
                            <th class="px-4 py-3">{{ __('Role') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($team->members as $member)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $member->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $member->email }}</td>
                                <td class="px-4 py-3 text-slate-600 capitalize">{{ $member->pivot->role ?? 'member' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($member->id !== $team->owner_id)
                                        <form method="POST" action="{{ route('admin.teams.members.remove', [$team, $member]) }}" onsubmit="return confirm('{{ __('Remove member?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm text-rose-600 hover:text-rose-800">{{ __('Remove') }}</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">{{ __('Owner') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">{{ __('No members.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Subscriptions') }}</h2>
                <div class="space-y-3">
                    @forelse ($team->toolSubscriptions as $subscription)
                        <div class="rounded-lg bg-slate-50 p-3 text-sm">
                            <div class="font-medium text-slate-900">{{ $subscription->plan?->name ?? '—' }}</div>
                            <div class="text-slate-600 capitalize">{{ $subscription->payment_method }} · {{ $subscription->billing_interval }}</div>
                            <div class="text-slate-500 text-xs mt-1">{{ __('Status') }}: <span class="font-medium">{{ $subscription->status }}</span></div>
                            <div class="text-slate-500 text-xs">{{ __('Valid until') }}: {{ $subscription->ends_at?->format('d.m.Y') ?? '—' }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('No subscriptions.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
