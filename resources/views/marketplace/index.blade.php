@extends('layouts.shell', ['title' => __('Marketplace')])

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Module Marketplace') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Explore and subscribe to the tools that fit your workflow.') }}</p>
</div>

@foreach ($grouped as $category => $modules)
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ $category }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $module)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-slate-900">{{ $module['name'] }}</h3>
                        @if ($user->hasModule($module['key']))
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Active') }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $module['description'] }}</p>

                    @if (! empty($module['features']))
                        <ul class="mt-3 space-y-1">
                            @foreach ($module['features'] as $feature)
                                <li class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-4 flex items-center gap-3">
                        <a href="{{ route('marketplace.show', $module['key']) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Preview') }}</a>
                        @if (! $user->hasModule($module['key']))
                            <a href="{{ route('billing.plans', ['module' => $module['key']]) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Subscribe') }}</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
@endsection
