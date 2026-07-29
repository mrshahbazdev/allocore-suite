@extends('layouts.shell')

@section('title', __('Edit Cluster'))
@section('page-title', __('Edit Cluster'))

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('ClusterForge') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Cluster') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $cluster->name }}</p>
            </div>
            <a href="{{ route('clusterforge.show', $cluster) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back') }}</a>
        </div>

        <form method="POST" action="{{ route('clusterforge.update', $cluster) }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Project Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $cluster->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $cluster->description) }}</textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Algorithm') }}</label>
                    <select name="algorithm" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="terms" {{ old('algorithm', $cluster->algorithm) === 'terms' ? 'selected' : '' }}>{{ __('Shared Terms') }}</option>
                        <option value="similarity" {{ old('algorithm', $cluster->algorithm) === 'similarity' ? 'selected' : '' }}>{{ __('Semantic Similarity') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Tags') }}</label>
                    <input type="text" name="tags" value="{{ old('tags', implode(', ', $cluster->tags ?? [])) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('seo, content') }}">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Keywords') }}</label>
                <textarea name="keywords" rows="6" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Leave blank to keep current keywords') }}">{{ old('keywords', implode("\n", $cluster->keywords ?? [])) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep current keywords. Entering new keywords will re-run clustering.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Keywords File') }}</label>
                <input type="file" name="keywords_file" accept=".csv,.txt" class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', $cluster->is_public) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_public" class="text-sm text-slate-700">{{ __('Make public and shareable') }}</label>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Update Cluster') }}</button>
            </div>
        </form>
    </div>
@endsection
