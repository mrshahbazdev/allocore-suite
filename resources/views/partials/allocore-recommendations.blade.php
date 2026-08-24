@if (isset($recommendations) && count($recommendations['items'] ?? []) > 0)
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">{{ __('Next steps') }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $recommendations['headline'] ?? '' }}</p>
        <ul class="mt-4 space-y-4">
            @foreach ($recommendations['items'] as $item)
                <li class="rounded-lg border {{ $item['is_first'] ? 'border-indigo-300 bg-indigo-50' : 'border-slate-100 bg-slate-50' }} p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $item['is_first'] ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700' }} text-xs font-bold">{{ $item['priority'] }}</span>
                                <p class="font-semibold text-slate-900">{{ __($item['pillar']) }}</p>
                                @if ($item['is_first'])
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ __('Address first') }}</span>
                                @endif
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $item['score'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{!! $item['action_html'] ?? e(__($item['action'])) !!}</p>
                            @if ($item['module_name'])
                                <p class="mt-1 text-sm text-slate-500">{{ __('Recommended tool:') }} <span class="font-medium text-slate-900">{{ $item['module_name'] }}</span></p>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2 sm:items-end">
                            @if ($item['module_name'])
                                @if ($item['subscribed'])
                                    <a href="{{ $item['module_route'] }}" class="shrink-0 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Open') }}</a>
                                @else
                                    <a href="{{ route('billing.plans', ['module' => $item['module_key']]) }}" class="shrink-0 rounded-lg border border-indigo-600 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">{{ __('Add') }}</a>
                                @endif
                            @endif
                            <form method="POST" action="{{ route('audit.startSmall') }}" class="inline">
                                @csrf
                                <input type="hidden" name="focus_pillar" value="{{ $item['pillar'] }}">
                                <button type="submit" class="shrink-0 rounded-lg border border-emerald-600 px-3 py-1.5 text-sm font-semibold text-emerald-600 hover:bg-emerald-50">{{ __('Start free small audit') }}</button>
                            </form>
                        </div>
                    </div>

                    @if (! empty($item['glossary_terms']) && $item['glossary_terms']->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($item['glossary_terms'] as $term)
                                <a href="{{ route('knowledge.show', $term->slug) }}" class="inline-flex items-center gap-1 rounded-full border border-[#ff9200]/20 bg-[#ff9200]/5 px-2 py-0.5 text-xs font-medium text-[#ff9200] hover:bg-[#ff9200]/10">
                                    {{ $term->term }}
                                    @if ($term->is_beginner_friendly)
                                        <span class="text-[10px] text-emerald-600">({{ __('Easy') }})</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

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
