@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('My Dashboards') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Create custom dashboards and drag widgets into your preferred layout.') }}</p>
            </div>
            <a href="{{ route('dashboards.create') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Create dashboard') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        @if ($dashboards->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                <p>{{ __('No dashboards yet.') }}</p>
                <a href="{{ route('dashboards.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline">{{ __('Create your first dashboard') }}</a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($dashboards as $dashboard)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $dashboard->title }}</h3>
                                <p class="text-xs text-slate-500">{{ count($dashboard->widgets ?? []) }} {{ __('widgets') }}</p>
                            </div>
                            @if ($dashboard->is_default)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('dashboards.show', $dashboard) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Open') }}</a>
                            <a href="{{ route('dashboards.edit', $dashboard) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('dashboards.destroy', $dashboard) }}" onsubmit="return confirm('{{ __('Delete this dashboard?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
