@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Workspace') }}</h1>
            <p class="text-sm text-slate-500">{{ __('All your active tools, recent records, and cross-tool insights in one place.') }}</p>
        </div>
        @if ($next_step)
            <a href="{{ $next_step['link'] }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Next step: :step', ['step' => $next_step['label']]) }}</a>
        @endif
    </div>

    @if ($next_step)
        <div class="mb-6 rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3 text-sm text-indigo-800">
            {{ __('Complete your setup: :step', ['step' => $next_step['label']]) }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('My Tools') }}</h2>
                @if (empty($modules))
                    <p class="text-sm text-slate-500">{{ __('No active tools yet.') }} <a href="{{ route('tools.index') }}" class="text-indigo-600 hover:underline">{{ __('Browse tools') }}</a></p>
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($modules as $module)
                            <div class="rounded-lg border border-slate-200 p-4 hover:border-indigo-300 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-900">{{ $module['name'] }}</h3>
                                        <p class="text-xs text-slate-500">{{ $module['count'] }} {{ mb_strtolower($module['label'] ?? 'records') }}</p>
                                    </div>
                                    <a href="{{ $module['setup_link'] }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">{{ __('Open') }}</a>
                                </div>
                                @if (! empty($module['recent']))
                                    <ul class="mt-3 divide-y divide-slate-100">
                                        @foreach ($module['recent'] as $record)
                                            <li class="py-2 flex items-center justify-between text-sm">
                                                <a href="{{ $record['url'] ?? $module['setup_link'] }}" class="truncate text-slate-700 hover:text-indigo-600 max-w-[75%]">{{ $record['title'] }}</a>
                                                <span class="text-xs text-slate-400 whitespace-nowrap">{{ $record['created_at'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-3 text-xs text-slate-400">{{ __('No records yet.') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Onboarding checklist') }}</h2>
                <div class="space-y-2">
                    @foreach ($onboarding as $step)
                        <div class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $step['complete'] ? 'bg-emerald-50' : 'bg-slate-50' }}">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full {{ $step['complete'] ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-white' }} text-xs">
                                @if ($step['complete']) &check; @else &bull; @endif
                            </span>
                            <span class="flex-1 text-sm {{ $step['complete'] ? 'text-slate-500 line-through' : 'text-slate-700' }}">{{ $step['label'] }}</span>
                            @if (! $step['complete'])
                                <a href="{{ $step['link'] }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">{{ __('Start') }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Cross-tool insights') }}</h2>
                <div class="space-y-3">
                    @forelse ($insights as $insight)
                        <div class="rounded-lg border {{ $insight['unlocked'] ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200 bg-slate-50' }} p-3">
                            <h3 class="text-sm font-semibold {{ $insight['unlocked'] ? 'text-indigo-900' : 'text-slate-600' }}">{{ $insight['title'] }}</h3>
                            <p class="mt-1 text-xs text-slate-600">{{ $insight['description'] }}</p>
                            @if (! $insight['accessible'])
                                <p class="mt-2 text-xs text-amber-600">{{ __('Subscribe to :module to unlock.', ['module' => ucfirst(str_replace(['-','_'],' ',$insight['missing_module']))]) }}</p>
                            @elseif (! $insight['unlocked'])
                                <p class="mt-2 text-xs text-slate-500">{{ __('Add records to both tools to unlock this insight.') }}</p>
                            @else
                                <a href="{{ $insight['action'] }}" class="mt-2 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-500">{{ __('View connection') }}</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('Subscribe to more tools to unlock cross-tool insights.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
