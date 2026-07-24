@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $dashboard->title }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Custom dashboard') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboards.edit', $dashboard) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                <a href="{{ route('dashboards.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('All dashboards') }}</a>
            </div>
        </div>

        @if (empty($dashboard->widgets))
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                <p>{{ __('This dashboard has no widgets yet.') }}</p>
                <a href="{{ route('dashboards.edit', $dashboard) }}" class="mt-2 inline-block text-indigo-600 hover:underline">{{ __('Add widgets') }}</a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($dashboard->widgets as $widget)
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm {{ ($widget['type'] ?? '') === 'stats' ? 'lg:col-span-2' : '' }}">
                        <h3 class="mb-4 text-base font-semibold text-slate-900">{{ $widget['title'] ?? __('Widget') }}</h3>
                        @include('user-dashboards.widgets.' . ($widget['type'] ?? 'stats'), ['widget' => $widget])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
