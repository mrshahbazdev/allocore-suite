@extends('layouts.shell', ['title' => $module['name'] ?? $module->name])

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ $module->name }}</h1>
            @if (auth()->user()->hasModule($module->key))
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">{{ __('Active') }}</span>
            @endif
        </div>
        <p class="mt-2 text-slate-600">{{ $module->description }}</p>

        @if (! empty($details['features']))
            <h2 class="mt-6 text-lg font-semibold text-slate-900">{{ __('Key features') }}</h2>
            <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($details['features'] as $feature)
                    <li class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-6 flex items-center gap-3">
            <a href="{{ route('marketplace.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Back') }}</a>
            @if (! auth()->user()->hasModule($module->key))
                <a href="{{ route('billing.plans', ['module' => $module->key]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Subscribe') }}</a>
            @endif
        </div>
    </div>
</div>
@endsection
