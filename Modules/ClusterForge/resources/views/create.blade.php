@extends('layouts.shell')

@section('title', __('New Cluster'))
@section('page-title', __('ClusterForge'))

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('ClusterForge') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('New Cluster') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Enter a pillar topic and website to generate 5 subtopics, 50 Q&A, and publish-ready markdown pages.') }}</p>
            </div>
            <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back') }}</a>
        </div>

        @if (! $geminiConfigured)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                {{ __('Gemini API key is not configured. Add it in Settings > Gemini (admin) or set GEMINI_API_KEY in your .env file to generate clusters.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('clusterforge.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            <div>
                <label for="topic" class="block text-sm font-medium text-slate-700">{{ __('Pillar topic') }}</label>
                <input type="text" name="topic" id="topic" value="{{ old('topic') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('e.g. content marketing for SaaS') }}">
                @error('topic')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="website" class="block text-sm font-medium text-slate-700">{{ __('Website or audience') }}</label>
                <input type="text" name="website" id="website" value="{{ old('website') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('e.g. allocore.de or a short description of the audience') }}">
                @error('website')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500" {{ $geminiConfigured ? '' : 'disabled' }}>{{ __('Generate cluster') }}</button>
            </div>
        </form>
    </div>
@endsection
