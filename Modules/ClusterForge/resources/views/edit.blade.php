@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('clusterforge.edit_project') }}</h1>
    </div>

    <form method="POST" action="{{ route('clusterforge.update', $cluster) }}" enctype="multipart/form-data" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.project_name') }}</label>
            <input type="text" name="name" value="{{ old('name', $cluster->name) }}" required class="mt-2 w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.description_label') }}</label>
            <textarea name="description" rows="2" class="mt-2 w-full rounded-lg border-slate-300 text-sm">{{ old('description', $cluster->description) }}</textarea>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.algorithm') }}</label>
                <select name="algorithm" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                    <option value="terms" {{ old('algorithm', $cluster->algorithm) === 'terms' ? 'selected' : '' }}>{{ __('clusterforge.terms_algorithm') }}</option>
                    <option value="similarity" {{ old('algorithm', $cluster->algorithm) === 'similarity' ? 'selected' : '' }}>{{ __('clusterforge.similarity_algorithm') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.tags') }}</label>
                <input type="text" name="tags" value="{{ old('tags', implode(', ', $cluster->tags ?? [])) }}" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.keywords') }}</label>
            <textarea name="keywords" rows="6" class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('clusterforge.leave_blank_to_keep') }}">{{ old('keywords', implode("\n", $cluster->keywords ?? [])) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">{{ __('clusterforge.leave_blank_to_keep') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.keywords_file') }}</label>
            <input type="file" name="keywords_file" accept=".csv,.txt" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', $cluster->is_public) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
            <label for="is_public" class="text-sm text-slate-700">{{ __('clusterforge.make_public') }}</label>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('clusterforge.update') }}</button>
        </div>
    </form>
@endsection
