@extends('layouts.shell', ['title' => __('Organizations')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Organizations') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage VisionFlow organizations.') }}</p>
        </div>
        <a href="{{ route('visionflow.organizations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('New Organization') }}
        </a>
    </div>

    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6"/></svg></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No organizations yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Create a demo organization or start with your own.') }}</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($organizations as $organization)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-lg font-semibold text-slate-900">{{ $organization->name }}</div>
                            <div class="text-sm text-slate-500">{{ $organization->description ?: __('No description') }}</div>
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('visionflow.organizations.edit', $organization) }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600" title="{{ __('Edit') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            </a>
                            <form method="POST" action="{{ route('visionflow.organizations.destroy', $organization) }}" onsubmit="return confirm('{{ __('Delete this organization?') }}')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="{{ __('Delete') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397M4.772 5.79L5.42 3.356A2.25 2.25 0 017.47 2.25h9.06a2.25 2.25 0 012.05 2.106l.648 2.434m-14.456 0a48.11 48.11 0 013.478-.397"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 text-sm">
                        <span class="rounded-lg bg-rose-50 px-2 py-1 text-rose-700">{{ $organization->values_count }} {{ __('values') }}</span>
                        <span class="rounded-lg bg-sky-50 px-2 py-1 text-sky-700">{{ $organization->principles_count }} {{ __('principles') }}</span>
                        <span class="rounded-lg bg-amber-50 px-2 py-1 text-amber-700">{{ $organization->strategic_goals_count }} {{ __('goals') }}</span>
                    </div>
                    <a href="{{ route('visionflow.organizations.show', $organization) }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        {{ __('Open Organization') }}
                        <svg class="h-4 w-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
