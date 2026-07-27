@if (isset($recommendations) && count($recommendations['items'] ?? []) > 0)
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">{{ __('Next steps') }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $recommendations['headline'] ?? '' }}</p>
        <ul class="mt-4 space-y-4">
            @foreach ($recommendations['items'] as $item)
                <li class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-900">{{ $item['pillar'] }}</p>
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $item['score'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{{ __($item['action']) }}</p>
                            @if ($item['module_name'])
                                <p class="mt-1 text-sm text-slate-500">{{ __('Recommended tool:') }} <span class="font-medium text-slate-900">{{ $item['module_name'] }}</span></p>
                            @endif
                        </div>
                        @if ($item['module_name'])
                            @if ($item['subscribed'])
                                <a href="{{ $item['module_route'] }}" class="shrink-0 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Open') }}</a>
                            @else
                                <a href="{{ route('billing.plans', ['module' => $item['module_key']]) }}" class="shrink-0 rounded-lg border border-indigo-600 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">{{ __('Add') }}</a>
                            @endif
                        @endif
                    </div>

                    @if (! empty($item['kpis']))
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($item['kpis'] as $kpi)
                                <div class="rounded-md bg-white p-3 shadow-sm">
                                    <p class="text-xs font-medium text-slate-500">{{ $kpi['label'] }}</p>
                                    <div class="mt-2 flex items-end justify-between">
                                        <span class="text-xl font-bold text-slate-900">{{ $kpi['current'] }}{{ $kpi['unit'] === '%' ? '%' : '' }}</span>
                                        <span class="text-xs text-slate-500">{{ __('Target') }}: {{ $kpi['target'] }}{{ $kpi['unit'] === '%' ? '%' : '' }}</span>
                                    </div>
                                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $kpi['progress'] >= 80 ? 'bg-emerald-500' : ($kpi['progress'] >= 50 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ min(100, $kpi['progress']) }}%"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Gap') }}: {{ $kpi['gap'] }}{{ $kpi['unit'] === '%' ? '%' : '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
