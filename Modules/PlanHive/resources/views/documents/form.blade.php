@extends('layouts.shell')

@section('title', $document->exists ? __('Edit Document') : __('Upload Document'))
@section('page-title', $document->exists ? __('Edit Document') : __('Upload Document'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $document->exists ? __('Edit Document') : __('Upload Document') }}</h1>
        <form method="POST" action="{{ $document->exists ? route('planhive.documents.update', $document) : route('planhive.documents.store', $project) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            @if ($document->exists)
                @method('PUT')
            @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $document->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            @if (! $document->exists)
                <div><label class="block text-sm font-medium text-slate-700">{{ __('File') }}</label><input type="file" name="file" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            @endif
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
