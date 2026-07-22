@extends('layouts.shell')

@section('title', __('BunnyBand Users'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Users') }}</h1>

        <form method="GET" class="flex gap-2"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search" class="rounded-lg border-slate-300"><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Search') }}</button></form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Email') }}</th><th class="pb-2 pr-4">{{ __('Balance') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($profiles as $profile)
                        <tr>
                            <td class="py-2 pr-4">{{ $profile->user->name }}</td>
                            <td class="py-2 pr-4">{{ $profile->user->email }}</td>
                            <td class="py-2 pr-4">{{ number_format($profile->balance, 2) }}</td>
                            <td class="py-2 pr-4">{{ $profile->is_blocked ? __('Blocked') : __('Active') }}</td>
                            <td class="py-2"><a href="{{ route('bunnyband.admin.users.show', $profile) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $profiles->links() }}</div>
        </div>
    </div>
@endsection
