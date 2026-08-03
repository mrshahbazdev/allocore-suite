@extends('layouts.shell')

@section('title', __('Notes'))
@section('page-title', $project->name.' — '.__('Notes'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <a href="{{ route('planhive.projects.show', $project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ $project->name }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Notes') }}</h1>
            </div>
            <a href="{{ route('planhive.notes.create', $project) }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Note') }}</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($notes as $note)
                <a href="{{ route('planhive.notes.show', $note) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                    <h3 class="font-semibold text-slate-900">{{ $note->title }}</h3>
                    <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $note->content }}</p>
                    <div class="mt-3 text-xs text-slate-400">{{ $note->created_at->format('M d, Y') }}</div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">{{ __('No notes yet.') }}</div>
            @endforelse
        </div>

        <div>{{ $notes->links() }}</div>
    </div>
@endsection
