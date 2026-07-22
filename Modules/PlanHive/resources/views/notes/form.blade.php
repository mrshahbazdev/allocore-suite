@extends('layouts.shell')

@section('title', $note->exists ? __('Edit Note') : __('New Note'))
@section('page-title', $note->exists ? __('Edit Note') : __('New Note'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $note->exists ? __('Edit Note') : __('New Note') }}</h1>
        <form method="POST" action="{{ $note->exists ? route('planhive.notes.update', $note) : route('planhive.notes.store', $project) }}" class="mt-6 space-y-4">
            @csrf
            @if ($note->exists)
                @method('PUT')
            @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $note->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Content') }}</label><textarea name="content" rows="8" class="mt-1 w-full rounded-lg border-slate-300">{{ old('content', $note->content) }}</textarea></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
