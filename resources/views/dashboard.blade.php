@extends('layouts.shell')

@section('content')
    @if ($announcements->isNotEmpty())
        <div class="mb-6 space-y-3">
            @foreach ($announcements as $announcement)
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-900">
                    <h2 class="font-semibold">{{ $announcement->title }}</h2>
                    <p class="mt-1 text-sm text-indigo-800">{{ $announcement->body }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Overview') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Analytics across all your subscribed tools.') }}</p>
        </div>
        <a href="{{ route('tool-analyzer.index') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Analyze my tools') }}</a>
    </div>

    @php($activeModules = $modules->filter(fn ($m) => in_array($m->key, $accessible))->values())
    @php($lockedModules = $modules->filter(fn ($m) => ! in_array($m->key, $accessible))->values())

    {{-- Active tools --}}
    <h2 class="text-lg font-semibold text-slate-900 mb-3">{{ __('My Tools') }} ({{ $activeModules->count() }})</h2>
    @if ($activeModules->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 mb-8">
            {{ __('You have no active tools yet.') }} <a href="{{ route('billing.plans') }}" class="text-indigo-600 hover:underline">{{ __('Browse plans') }}</a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
            @foreach ($activeModules as $module)
                <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <h3 class="font-semibold text-slate-900">{{ $module->name }}</h3>
                        <span class="text-[10px] rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 font-medium">{{ __('Active') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                    <div class="mt-4">
                        <a href="{{ url('app/'.$module->route_prefix) }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('Open tool') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Locked tools --}}
    @if ($lockedModules->isNotEmpty())
        <h2 class="text-lg font-semibold text-slate-900 mb-3">{{ __('Available add-ons') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
            @foreach ($lockedModules as $module)
                <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm opacity-90">
                    <div class="flex items-start justify-between">
                        <h3 class="font-semibold text-slate-900">{{ $module->name }}</h3>
                        <span class="text-[10px] rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 font-medium">{{ __('Locked') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($module->description, 80) }}</p>
                    <div class="mt-4">
                        <a href="{{ route('billing.plans', ['module' => $module->key]) }}" class="inline-flex items-center rounded-lg border border-indigo-600 px-3 py-1.5 text-sm font-medium text-indigo-600 hover:bg-indigo-50">{{ __('Subscribe') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Module analytics widgets --}}
    @if (count($widgets))
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Tool Analytics') }}</h2>
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($widgets as $moduleKey => $widgetView)
                @include($widgetView)
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
            {{ __('Subscribe to a tool to see its analytics here.') }}
        </div>
    @endif
@endsection
