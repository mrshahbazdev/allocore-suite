@php($title = __('DataForSEO Settings'))

@extends('layouts.shell')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-900">{{ __('DataForSEO Settings') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Configure the DataForSEO API credentials used to enrich ClusterForge keyword metrics.') }}</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.dataforseo.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('API login') }}</label>
                            <input name="login" type="text" value="{{ old('login', $login) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="your@email.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('API password') }}</label>
                            <input name="password" type="password" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ $hasPassword ? '••••••••' : '' }}">
                            @if ($hasPassword)
                                <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep the current password.') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('API base URL') }}</label>
                            <input name="base_url" type="text" value="{{ old('base_url', $baseUrl) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Request timeout (seconds)') }}</label>
                            <input name="timeout" type="number" min="1" max="300" value="{{ old('timeout', $timeout) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Cache TTL (seconds)') }}</label>
                            <input name="cache_ttl" type="number" min="0" max="604800" value="{{ old('cache_ttl', $cacheTtl) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
