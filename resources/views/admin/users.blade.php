@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Users') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage access, roles, and active teams.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('User') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Roles') }}</th>
                    <th class="px-4 py-3">{{ __('Subscriptions') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->currentTeam?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->roles->pluck('name')->join(', ') ?: 'member' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->toolSubscriptions->count() }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="role" class="rounded-lg border-slate-300 text-sm">
                                    <option value="member" @selected(! $user->hasRole('admin'))>{{ __('Member') }}</option>
                                    <option value="admin" @selected($user->hasRole('admin'))>{{ __('Admin') }}</option>
                                </select>
                                <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('No users found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
