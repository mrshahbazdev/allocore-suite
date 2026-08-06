@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Knowledge Manager') }}</h1>
        <a href="{{ route('knowledgemanager.projects.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('New Project') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Projects') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#0094af]">{{ $stats['projects'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Published') }}</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['published'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Answers') }}</p>
            <p class="mt-2 text-3xl font-bold text-[#ff9200]">{{ $stats['answers'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Assets') }}</p>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['assets'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Projects') }}</h2>
        @if($projects->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No knowledge projects yet.') }}</p>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach($projects as $project)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <a href="{{ route('knowledgemanager.projects.show', $project) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $project->name }}</a>
                            <p class="text-xs text-slate-500">{{ $project->status }} &middot; {{ $project->progress() }}% {{ __('completed') }}</p>
                        </div>
                        <a href="{{ route('knowledgemanager.projects.show', $project) }}" class="text-sm text-[#0094af] hover:underline">{{ __('View') }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
