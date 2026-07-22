@extends('layouts.shell')

@section('title', $project->name)
@section('page-title', $project->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <div class="flex items-center gap-2">
                    <span class="h-4 w-4 rounded-full" style="background-color: {{ $project->color }}"></span>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $project->name }}</h1>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $project->description }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('planhive.projects.edit', $project) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.projects.destroy', $project) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Members') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($project->members as $member)
                    <li class="flex items-center justify-between rounded-lg border border-slate-200 p-2">
                        <span>{{ $member->name }} <span class="text-xs text-slate-500">({{ $member->pivot->role }})</span></span>
                        <form method="POST" action="{{ route('planhive.projects.members.remove', [$project, $member]) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="text-rose-600">{{ __('Remove') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('planhive.projects.members.add', $project) }}" class="mt-4 flex items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Member Email') }}</label>
                    <input type="email" name="email" class="mt-1 rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Role') }}</label>
                    <select name="role" class="mt-1 rounded-lg border-slate-300">
                        <option value="member">{{ __('Member') }}</option>
                        <option value="manager">{{ __('Manager') }}</option>
                        <option value="boss">{{ __('Boss') }}</option>
                    </select>
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add') }}</button>
            </form>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">{{ __('Tasks') }}</h2><a href="{{ route('planhive.tasks.create', $project) }}" class="text-sm text-indigo-600">{{ __('New') }}</a></div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($project->tasks->take(10) as $task)
                        <li class="flex items-center justify-between rounded-lg border border-slate-200 p-2">
                            <span>{{ $task->title }}</span>
                            <span class="text-xs text-slate-500">{{ __($task->status) }} — {{ $task->assignee?->name ?? '-' }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No tasks.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">{{ __('Goals') }}</h2><a href="{{ route('planhive.goals.create', $project) }}" class="text-sm text-indigo-600">{{ __('New') }}</a></div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($project->goals as $goal)
                        <li>
                            <div class="flex items-center justify-between"><span>{{ $goal->title }}</span><span class="text-xs text-slate-500">{{ $goal->progress }}%</span></div>
                            <div class="mt-1 h-1.5 w-full rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-600" style="width: {{ $goal->progress }}%"></div></div>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No goals.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">{{ __('Notes') }}</h2><a href="{{ route('planhive.notes.create', $project) }}" class="text-sm text-indigo-600">{{ __('New') }}</a></div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($project->notes as $note)
                        <li><a href="{{ route('planhive.notes.edit', $note) }}" class="text-indigo-600">{{ $note->title }}</a></li>
                    @empty
                        <li class="text-slate-500">{{ __('No notes.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">{{ __('Documents') }}</h2><a href="{{ route('planhive.documents.create', $project) }}" class="text-sm text-indigo-600">{{ __('Upload') }}</a></div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($project->documents as $document)
                        <li class="flex items-center justify-between"><a href="{{ route('planhive.documents.download', $document) }}" class="text-indigo-600">{{ $document->title }}</a><span class="text-xs text-slate-500">{{ $document->readable_size }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No documents.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">{{ __('Contacts') }}</h2><a href="{{ route('planhive.contacts.create', $project) }}" class="text-sm text-indigo-600">{{ __('New') }}</a></div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($project->contacts as $contact)
                        <li class="flex justify-between"><a href="{{ route('planhive.contacts.edit', $contact) }}" class="text-indigo-600">{{ $contact->name }}</a><span class="text-slate-500">{{ $contact->company }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No contacts.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">{{ __('Calendar') }}</h2><a href="{{ route('planhive.calendar-events.create', $project) }}" class="text-sm text-indigo-600">{{ __('New Event') }}</a></div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($project->calendarEvents->take(5) as $event)
                        <li class="flex justify-between"><span>{{ $event->title }}</span><span class="text-xs text-slate-500">{{ $event->start_at->format('M d H:i') }}</span></li>
                    @empty
                        <li class="text-slate-500">{{ __('No events.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
