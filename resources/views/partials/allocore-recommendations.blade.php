@if (isset($recommendations) && count($recommendations['items'] ?? []) > 0)
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">{{ __('Next steps') }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $recommendations['headline'] ?? '' }}</p>
        <ul class="mt-4 space-y-3">
            @foreach ($recommendations['items'] as $item)
                <li class="flex items-start justify-between gap-4 rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $item['pillar'] }} — {{ $item['score'] }}</p>
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
                </li>
            @endforeach
        </ul>
    </div>
@endif
