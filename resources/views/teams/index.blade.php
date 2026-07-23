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
                <input type="text" name="industry" placeholder="{{ __('Industry') }}" class="w-full rounded-lg border-slate-300 text-sm">
                <input type="text" name="size" placeholder="{{ __('Company size, e.g. 10–50') }}" class="w-full rounded-lg border-slate-300 text-sm">
                @error('name')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create team') }}</button>
            </form>

            @if (auth()->user()->currentTeam && auth()->user()->currentTeam->owner_id === auth()->id())
                <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                    <h2 class="font-semibold text-slate-900 mb-4">{{ __('Team members') }}</h2>
                    <ul class="space-y-3">
                        @foreach (auth()->user()->currentTeam->members()->where('users.id', '!=', auth()->id())->get() as $member)
                            <li class="flex items-center justify-between rounded-lg border border-slate-100 px-4 py-3">
                                <div>
                                    <div class="font-medium text-slate-900">{{ $member->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $member->email }}</div>
                                </div>
                                <a href="{{ route('teams.members.permissions.edit', [auth()->user()->currentTeam, $member]) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Permissions') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form method="POST" action="{{ route('teams.update', auth()->user()->currentTeam) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-4">
                    @csrf
                    @method('PUT')
                    <h2 class="font-semibold text-slate-900">{{ __('Team profile') }}</h2>
                    <input type="text" name="name" required value="{{ auth()->user()->currentTeam->name }}" placeholder="{{ __('Team name') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    <input type="text" name="industry" value="{{ auth()->user()->currentTeam->industry }}" placeholder="{{ __('Industry') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    <input type="text" name="size" value="{{ auth()->user()->currentTeam->size }}" placeholder="{{ __('Company size, e.g. 10–50') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update profile') }}</button>
                </form>

                <form method="POST" action="{{ route('teams.members.add', auth()->user()->currentTeam) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-4">
                    @csrf
                    <h2 class="font-semibold text-slate-900">{{ __('Add member to :team', ['team' => auth()->user()->currentTeam->name]) }}</h2>
                    <input type="email" name="email" required placeholder="{{ __('Member email') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    @error('email')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add member') }}</button>
                </form>

                <form method="POST" action="{{ route('teams.invitations.store', auth()->user()->currentTeam) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-4">
                    @csrf
                    <h2 class="font-semibold text-slate-900">{{ __('Invite to :team', ['team' => auth()->user()->currentTeam->name]) }}</h2>
                    <input type="email" name="email" required placeholder="{{ __('Invitee email') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    <select name="role" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="member">{{ __('Member') }}</option>
                        <option value="admin">{{ __('Admin') }}</option>
                    </select>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Send invitation') }}</button>
                </form>

                @php($invitations = auth()->user()->currentTeam->invitations()->whereNull('accepted_at')->latest()->get())
                @if ($invitations->isNotEmpty())
                    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                        <h2 class="font-semibold text-slate-900 mb-4">{{ __('Pending invitations') }}</h2>
                        <ul class="space-y-3">
                            @foreach ($invitations as $invitation)
                                <li class="flex items-center justify-between rounded-lg border border-slate-100 px-4 py-3">
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $invitation->email }}</div>
                                        <div class="text-xs text-slate-500">{{ __('Expires') }}: {{ $invitation->expires_at->format('d.m.Y') }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('teams.invitations.resend', $invitation) }}">
                                            @csrf
                                            <button class="text-sm text-indigo-600 hover:underline">{{ __('Resend') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('teams.invitations.destroy', $invitation) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm text-rose-600 hover:underline">{{ __('Cancel') }}</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
