@extends('layouts.shell')

@section('title', $note->title)
@section('page-title', $note->title)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <a href="{{ route('planhive.notes.index', $note->project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Notes') }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $note->title }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('planhive.notes.edit', $note) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.notes.destroy', $note) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-sm text-slate-500">{{ $note->created_at->format('M d, Y H:i') }}</div>
            <p class="mt-4 whitespace-pre-line text-slate-700">{{ $note->content }}</p>
        </div>
    </div>
@endsection
