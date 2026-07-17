@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.users.edit_title', ['name' => $user->name]) }}</h1>
        <p class="text-sm text-slate-500">{{ $user->email }}</p>
    </div>

    <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                <input name="password" type="password" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">{{ __('admin.users.password_blank') }}</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.users.role') }}</label>
                    <select name="role" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="user" @selected(! $user->hasRole('admin'))>{{ __('User') }}</option>
                        <option value="admin" @selected($user->hasRole('admin'))>{{ __('Admin') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.users.current_team') }}</label>
                    <select name="current_team_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('admin.users.no_team') }}</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('current_team_id', $user->current_team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="email_verified" name="email_verified" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('email_verified', $user->hasVerifiedEmail()))>
                <label for="email_verified" class="text-sm font-medium text-slate-700">{{ __('admin.users.email_verified') }}</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.users.save_button') }}</button>
            </div>
        </form>
    </div>
@endsection
