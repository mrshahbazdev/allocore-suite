@extends('layouts.shell')

@section('title', $document->title)
@section('page-title', $document->title)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <a href="{{ route('planhive.documents.index', $document->project) }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Documents') }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $document->title }}</h1>
            </div>
            <div class="flex gap-2">
                @if ($document->isImage())
                    <a href="{{ route('planhive.documents.preview', $document) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('View') }}</a>
                @endif
                <a href="{{ route('planhive.documents.download', $document) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Download') }}</a>
                <a href="{{ route('planhive.documents.edit', $document) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('planhive.documents.destroy', $document) }}" class="inline" onsubmit="return confirm('{{ __("Delete this document?") }}')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Project') }}</div>
                    <a href="{{ route('planhive.projects.show', $document->project) }}" class="font-medium text-indigo-600 hover:underline">{{ $document->project->name }}</a>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Size') }}</div>
                    <div class="font-medium text-slate-900">{{ $document->readable_size }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Type') }}</div>
                    <div class="font-medium text-slate-900">{{ $document->mime_type ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Uploaded') }}</div>
                    <div class="font-medium text-slate-900">{{ $document->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>

            @if ($document->isImage())
                <div class="mt-6">
                    <img src="{{ route('planhive.documents.preview', $document) }}" alt="{{ $document->title }}" class="max-h-[60vh] rounded-lg border border-slate-200 object-contain">
                </div>
            @endif
        </div>
    </div>
@endsection
