@extends('layouts.shell')

@section('title', __('Documents'))
@section('page-title', $project->name.' — '.__('Documents'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <a href="{{ route('planhive.projects.show', $project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ $project->name }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Documents') }}</h1>
            </div>
            <a href="{{ route('planhive.documents.create', $project) }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Upload Document') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($documents as $document)
                <div class="flex flex-col gap-2 border-b border-slate-100 p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('planhive.documents.show', $document) }}" class="font-semibold text-slate-900 hover:text-indigo-600 hover:underline">{{ $document->title }}</a>
                        <div class="mt-1 text-xs text-slate-500">{{ $document->mime_type ?? '-' }} — {{ $document->readable_size }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('planhive.documents.download', $document) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Download') }}</a>
                        <a href="{{ route('planhive.documents.edit', $document) }}" class="text-sm text-slate-600 hover:underline">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('planhive.documents.destroy', $document) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">{{ __('No documents yet.') }}</div>
            @endforelse
        </div>

        <div>{{ $documents->links() }}</div>
    </div>
@endsection
