@extends('layouts.shell', ['title' => __('Recommended Tools')])

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Recommended Tools') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Based on the tools you already use, here are the best next tools to subscribe to.') }}</p>
    </div>
    <a href="{{ route('billing.plans') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Browse plans') }}</a>
</div>

@if ($combos->isNotEmpty())
    <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Recommended combinations') }}</h2>
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($combos as $combo)
            <div class="rounded-xl bg-white border border-indigo-100 p-5 shadow-sm">
                <div class="mb-2 flex flex-wrap gap-2">
                    @foreach ($combo['owned_modules'] as $key)
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ \App\Models\Module::where('key', $key)->value('name') ?? ucfirst(str_replace(['-', '_'], ' ', $key)) }}</span>
                    @endforeach
                    @foreach ($combo['missing_modules'] as $key)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ \App\Models\Module::where('key', $key)->value('name') ?? ucfirst(str_replace(['-', '_'], ' ', $key)) }}</span>
                    @endforeach
                </div>
                <h3 class="font-semibold text-slate-900">{{ __('Unlock :module', ['module' => $combo['suggest_name']]) }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $combo['reason'] }}</p>
                <a href="{{ route('billing.plans', ['module' => $combo['suggest_key']]) }}" class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('Subscribe to :module', ['module' => $combo['suggest_name']]) }}</a>
            </div>
        @endforeach
    </div>
@endif

@if ($similar->isNotEmpty())
    <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Similar tools you may like') }}</h2>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($similar as $module)
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <h3 class="font-semibold text-slate-900">{{ $module['name'] }}</h3>
                    <span class="text-[10px] rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 font-medium">{{ __('Similar') }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $module['description'] }}</p>
                <p class="mt-2 text-xs text-slate-400">{{ $module['reason'] }}</p>
                <a href="{{ route('billing.plans', ['module' => $module['key']]) }}" class="mt-4 inline-flex items-center rounded-lg border border-indigo-600 px-3 py-1.5 text-sm font-medium text-indigo-600 hover:bg-indigo-50">{{ __('Subscribe') }}</a>
            </div>
        @endforeach
    </div>
@endif

@if ($similar->isEmpty() && $combos->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
        {{ __('Subscribe to at least one tool to unlock personalized recommendations.') }}
    </div>
@endif
@endsection
