@extends('layouts.shell', ['title' => __('Tool Analyzer')])

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Tool Analyzer') }}</h1>
        <p class="text-sm text-slate-500">{{ __('See which tools you own and which ones would complete your analysis.') }}</p>
    </div>

    @if ($deepKpiMissing)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="text-lg font-semibold text-amber-900">{{ __('Deep KPI analysis needs more tools') }}</h2>
            <p class="mt-2 text-sm text-amber-800">{{ __('The full Deep KPI report combines data from the tools below. Add them to unlock revenue, profit, order, influence and legacy insights.') }}</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($deepKpiMissing as $key)
                    @php($mod = $modules->firstWhere('key', $key))
                    <a href="{{ route('billing.plans', ['module' => $key]) }}" class="rounded-lg bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-200">{{ $mod?->name ?? $key }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Your active tools') }} ({{ $active->count() }})</h2>
            @if ($active->isEmpty())
                <p class="mt-4 text-slate-500">{{ __('No active tools yet.') }}</p>
            @else
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($active as $module)
                        <a href="{{ url('app/'.$module->route_prefix) }}" class="rounded-xl border border-slate-100 bg-slate-50 p-4 hover:border-indigo-200">
                            <div class="font-semibold text-slate-900">{{ $module->name }}</div>
                            <p class="mt-1 text-xs text-slate-600">{{ Str::limit($module->description, 80) }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recommended add-ons') }}</h2>
            @if ($suggested->isEmpty())
                <p class="mt-4 text-slate-500">{{ __('Great — you have all the recommended tools.') }}</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($suggested as $item)
                        <li class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $item['module']->name }}</div>
                                <p class="text-xs text-slate-600">{{ $item['reason'] }}</p>
                            </div>
                            <a href="{{ route('billing.plans', ['module' => $item['key']]) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Subscribe') }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @if ($locked->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('All available tools') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($locked as $module)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="font-semibold text-slate-900">{{ $module->name }}</div>
                        <p class="mt-1 text-xs text-slate-600">{{ Str::limit($module->description, 70) }}</p>
                        <a href="{{ route('billing.plans', ['module' => $module->key]) }}" class="mt-3 inline-block rounded-lg border border-indigo-600 px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">{{ __('Unlock') }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
