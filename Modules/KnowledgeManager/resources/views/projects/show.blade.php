@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $project->name }}</h1>
            <p class="text-sm text-slate-500">{{ $project->description }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('knowledgemanager.answers.edit', $project) }}" class="rounded-lg bg-[#0094af] px-4 py-2 text-sm font-semibold text-white hover:bg-[#00839c]">{{ __('Answer Questions') }}</a>
            <a href="{{ route('knowledgemanager.assets.index', $project) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Assets') }}</a>
            <a href="{{ route('knowledgemanager.documents.index', $project) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Documents') }}</a>
            <a href="{{ route('knowledgemanager.projects.edit', $project) }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Edit') }}</a>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-2 flex items-center justify-between text-sm">
            <span class="font-medium text-slate-700">{{ __('Completion') }}: {{ $project->progress() }}%</span>
            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $project->isPublished() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($project->status) }}</span>
        </div>
        <div class="h-3 w-full rounded-full bg-slate-100">
            <div class="h-3 rounded-full bg-[#0094af]" style="width: {{ $project->progress() }}%"></div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ __('URL') }}</p>
            <p class="mt-1 text-sm text-slate-900">{{ $project->url ?: '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ __('Industry') }}</p>
            <p class="mt-1 text-sm text-slate-900">{{ $project->industry ?: '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ __('Stage') }}</p>
            <p class="mt-1 text-sm text-slate-900">{{ $project->stage ?: '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ __('Answers') }}</p>
            <p class="mt-1 text-sm text-slate-900">{{ $project->answers_count }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Answered Sections') }}</h2>
        @foreach(config('knowledgemanager.sections') as $key => $section)
            @php($answered = $project->answers->where('section', $key)->whereNotNull('answer')->where('answer', '!=', '')->count())
            @php($total = count($section['questions']))
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm">
                    <span>{{ $section['label'] }}</span>
                    <span class="text-slate-500">{{ $answered }}/{{ $total }}</span>
                </div>
                <div class="mt-1 h-2 w-full rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-[#ff9200]" style="width: {{ $total > 0 ? ($answered / $total) * 100 : 0 }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
