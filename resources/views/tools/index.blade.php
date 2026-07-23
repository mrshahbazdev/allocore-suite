@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('All Tools') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage your active tools and subscribe to new ones.') }}</p>
        </div>
        <a href="{{ route('billing.plans') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Browse plans') }}</a>
    </div>

    @php($hasRecommendations = ! empty($recommendations['similar']) || ! empty($recommendations['combos']))

    @if ($hasRecommendations)
        <div class="mb-8 rounded-xl bg-indigo-50 border border-indigo-100 p-5">
            <div class="flex items-center justify-between gap-3 mb-3">
                <h2 class="text-lg font-semibold text-indigo-900">{{ __('Recommended for you') }}</h2>
                <a href="{{ route('recommendations.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('View all') }}</a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach (array_slice($recommendations['combos'], 0, 3) as $combo)
                    <div class="rounded-lg bg-white border border-indigo-100 p-4">
                        <h3 class="font-semibold text-slate-900">{{ $combo['suggest_name'] }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $combo['reason'] }}</p>
                        <a href="{{ route('billing.plans', ['module' => $combo['suggest_key']]) }}" class="mt-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500">{{ __('Subscribe') }}</a>
                    </div>
                @endforeach

                @foreach (array_slice($recommendations['similar'], 0, 3 - count(array_slice($recommendations['combos'], 0, 3))) as $module)
                    <div class="rounded-lg bg-white border border-slate-200 p-4">
                        <h3 class="font-semibold text-slate-900">{{ $module['name'] }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $module['reason'] }}</p>
                        <a href="{{ route('billing.plans', ['module' => $module['key']]) }}" class="mt-3 inline-flex items-center rounded-lg border border-indigo-600 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">{{ __('Subscribe') }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($accessible->isNotEmpty())
        <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('My Tools') }} ({{ $accessible->count() }})</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
            @foreach ($accessible as $module)
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

    @if ($locked->isNotEmpty())
        <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Available tools') }} ({{ $locked->count() }})</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($locked as $module)
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
@endsection
