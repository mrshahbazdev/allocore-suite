@if ($activeModules->isEmpty())
    <p class="text-sm text-slate-500">{{ __('You have no active tools yet.') }} <a href="{{ route('billing.plans') }}" class="text-indigo-600 hover:underline">{{ __('Browse plans') }}</a></p>
@else
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($activeModules as $module)
            <div class="rounded-xl border border-slate-200 p-4">
                <h4 class="font-semibold text-slate-900">{{ $module->name }}</h4>
                <p class="mt-1 text-xs text-slate-500">{{ Str::limit($module->description, 60) }}</p>
                @if (isset($moduleStats[$module->key]['count']))
                    <p class="mt-2 text-xs text-slate-500"><span class="font-semibold text-slate-900">{{ $moduleStats[$module->key]['count'] }}</span> {{ $moduleStats[$module->key]['label'] }}</p>
                @endif
                <a href="{{ url('app/'.$module->route_prefix) }}" class="mt-3 inline-flex items-center rounded-lg bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-indigo-700">{{ __('Open') }}</a>
            </div>
        @endforeach
    </div>
@endif
