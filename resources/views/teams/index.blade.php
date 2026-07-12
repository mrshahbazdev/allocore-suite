@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Teams') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Team subscriptions give every member access to the subscribed tools.') }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-4">{{ __('Your teams') }}</h2>
            <ul class="space-y-3">
                @forelse ($teams as $team)
                    <li class="flex items-center justify-between rounded-lg border border-slate-100 px-4 py-3">
                        <div>
                            <div class="font-medium text-slate-900">{{ $team->name }}</div>
                            <div class="text-xs text-slate-500">{{ __('Owner') }}: {{ $team->owner->name }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (auth()->user()->current_team_id === $team->id)
                                <span class="rounded-full bg-indigo-100 text-indigo-700 px-2 py-0.5 text-xs font-medium">{{ __('Current') }}</span>
                            @else
                                <form method="POST" action="{{ route('teams.switch', $team) }}">
                                    @csrf
                                    <button class="text-sm text-indigo-600 hover:underline">{{ __('Switch') }}</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-400">{{ __('You are not in any team yet.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('teams.store') }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-4">
                @csrf
                <h2 class="font-semibold text-slate-900">{{ __('Create a team') }}</h2>
                <input type="text" name="name" required placeholder="{{ __('Team name') }}" class="w-full rounded-lg border-slate-300 text-sm">
                @error('name')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create team') }}</button>
            </form>

            @if (auth()->user()->currentTeam && auth()->user()->currentTeam->owner_id === auth()->id())
                <form method="POST" action="{{ route('teams.members.add', auth()->user()->currentTeam) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-4">
                    @csrf
                    <h2 class="font-semibold text-slate-900">{{ __('Add member to :team', ['team' => auth()->user()->currentTeam->name]) }}</h2>
                    <input type="email" name="email" required placeholder="{{ __('Member email') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    @error('email')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add member') }}</button>
                </form>
            @endif
        </div>
    </div>
@endsection
