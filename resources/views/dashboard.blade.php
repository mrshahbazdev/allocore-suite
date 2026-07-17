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

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Overview') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Analytics across all your subscribed tools.') }}</p>
    </div>

    {{-- Module access cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
        @foreach ($modules as $module)
            @php($hasAccess = in_array($module->key, $accessible))
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <h3 class="font-semibold text-slate-900">{{ $module->name }}</h3>
                    @if ($hasAccess)
                        <span class="text-[10px] rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 font-medium">{{ __('Active') }}</span>
                    @else
                        <span class="text-[10px] rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 font-medium">{{ __('Locked') }}</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $module->description }}</p>
                <div class="mt-4">
                    @if ($hasAccess)
                        <a href="{{ url('app/'.$module->route_prefix) }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('Open tool') }}</a>
                    @else
                        <a href="{{ route('billing.plans', ['module' => $module->key]) }}" class="inline-flex items-center rounded-lg border border-indigo-600 px-3 py-1.5 text-sm font-medium text-indigo-600 hover:bg-indigo-50">{{ __('Subscribe') }}</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

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
