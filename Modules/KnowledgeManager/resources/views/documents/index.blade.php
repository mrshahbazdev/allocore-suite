@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $project->name }} — {{ __('Generated Documents') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Automatically generated knowledge artifacts from the captured answers and assets.') }}</p>
        </div>
        <a href="{{ route('knowledgemanager.projects.show', $project) }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Back') }}</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($documents as $key => $document)
            <a href="{{ route('knowledgemanager.documents.show', [$project, $key]) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-[#ff9200] hover:shadow-md">
                <h2 class="text-lg font-semibold text-slate-900">{{ $document['label'] }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $document['description'] }}</p>
                <span class="mt-4 inline-block text-sm font-medium text-[#0094af]">{{ __('Open document') }} →</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
