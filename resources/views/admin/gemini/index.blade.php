@php($title = __('Gemini Settings'))

@extends('layouts.shell')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-900">{{ __('Gemini Settings') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Configure the Gemini API key and generation parameters used by ClusterForge AI content generation.') }}</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.gemini.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">{{ __('API key') }}</label>
                            <input name="api_key" type="password" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ $hasApiKey ? '••••••••' : '' }}">
                            @if ($hasApiKey)
                                <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep the current API key.') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Model') }}</label>
                            <input name="model" type="text" value="{{ old('model', $model) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="gemini-flash-latest">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Base URL') }}</label>
                            <input name="base_url" type="text" value="{{ old('base_url', $baseUrl) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://generativelanguage.googleapis.com/v1beta">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Timeout (seconds)') }}</label>
                            <input name="timeout" type="number" min="1" max="600" value="{{ old('timeout', $timeout) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Max output tokens') }}</label>
                            <input name="max_output_tokens" type="number" min="1" max="100000" value="{{ old('max_output_tokens', $maxOutputTokens) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Max retries') }}</label>
                            <input name="max_retries" type="number" min="0" max="20" value="{{ old('max_retries', $maxRetries) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Retry base delay (ms)') }}</label>
                            <input name="retry_base_delay_ms" type="number" min="0" max="60000" value="{{ old('retry_base_delay_ms', $retryBaseDelayMs) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">{{ __('Fallback models (comma-separated)') }}</label>
                            <input name="fallback_models" type="text" value="{{ old('fallback_models', $fallbackModels) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="gemini-2.5-flash,gemini-2.0-flash,gemini-flash-latest">
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.integrations.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Save settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
