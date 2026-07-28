@extends('layouts.shell', ['title' => __('VisionFlow Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('VisionFlow') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Align values, principles, goals, vision and mission.') }}</p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('visionflow.organizations.demo') }}">
                @csrf
                <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Demo Organization') }}
                </button>
            </form>
            <a href="{{ route('visionflow.organizations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
                {{ __('New Organization') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.396a2.25 2.25 0 001.423-1.422l.396-1.183.396 1.183a2.25 2.25 0 001.423 1.423l1.183.396-1.183.396a2.25 2.25 0 00-1.423 1.422z"/></svg></div>
                <div><div class="text-sm text-slate-500">{{ __('Values') }}</div><div class="text-2xl font-bold text-slate-900">{{ $organizations->sum('values_count') }}</div></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg></div>
                <div><div class="text-sm text-slate-500">{{ __('Principles') }}</div><div class="text-2xl font-bold text-slate-900">{{ $organizations->sum('principles_count') }}</div></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></div>
                <div><div class="text-sm text-slate-500">{{ __('Goals') }}</div><div class="text-2xl font-bold text-slate-900">{{ $organizations->sum('strategic_goals_count') }}</div></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.214.13.428.183.641M9.954 9.784c-.463.598-.697 1.304-.697 2.016 0 .713.234 1.419.697 2.016m4.092-4c.463.597.697 1.303.697 2.016 0 .713-.234 1.419-.697 2.016M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                <div><div class="text-sm text-slate-500">{{ __('Visions') }}</div><div class="text-2xl font-bold text-slate-900">{{ $organizations->sum('visions_count') }}</div></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.28c-3.62.23-6.75-2.48-6.75-6.08 0-2.05.98-3.87 2.5-5.03 1.78-1.38 4.23-1.75 6.37-.97l-2.4 2.4H21M3 12h6"/></svg></div>
                <div><div class="text-sm text-slate-500">{{ __('Missions') }}</div><div class="text-2xl font-bold text-slate-900">{{ $organizations->sum('missions_count') }}</div></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25A2.25 2.25 0 018.25 10.5H6A2.25 2.25 0 013.75 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></div>
                <div><div class="text-sm text-slate-500">{{ __('Projects') }}</div><div class="text-2xl font-bold text-slate-900">{{ $organizations->sum('projects_count') }}</div></div>
            </div>
        </div>
    </div>

    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6"/></svg></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No organizations yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Create a demo organization or add your own to explore VisionFlow.') }}</p>
        </div>
    @else
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Organizations') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($organizations as $organization)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-lg font-semibold text-slate-900">{{ $organization->name }}</div>
                                <div class="text-sm text-slate-500">{{ $organization->description ?: __('No description') }}</div>
                            </div>
                            <a href="{{ route('visionflow.organizations.show', $organization) }}" class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600">
                                <svg class="h-5 w-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                            </a>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 text-sm">
                            <span class="rounded-lg bg-rose-50 px-2 py-1 text-rose-700">{{ $organization->values_count }} {{ __('values') }}</span>
                            <span class="rounded-lg bg-sky-50 px-2 py-1 text-sky-700">{{ $organization->principles_count }} {{ __('principles') }}</span>
                            <span class="rounded-lg bg-amber-50 px-2 py-1 text-amber-700">{{ $organization->strategic_goals_count }} {{ __('goals') }}</span>
                            <span class="rounded-lg bg-indigo-50 px-2 py-1 text-indigo-700">{{ $organization->visions_count }} {{ __('visions') }}</span>
                            <span class="rounded-lg bg-emerald-50 px-2 py-1 text-emerald-700">{{ $organization->missions_count }} {{ __('missions') }}</span>
                            <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-700">{{ $organization->projects_count }} {{ __('projects') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
