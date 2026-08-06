@php($answer = fn ($section, $key) => ($answers->get($section) ?? collect())->firstWhere('question_key', $key)?->answer ?? '')
@php($assetList = fn ($type) => $assets->get($type) ?? collect())

@extends('layouts.shell')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $document['label'] }}</h1>
            <p class="text-sm text-slate-500">{{ $project->name }}</p>
        </div>
        <a href="{{ route('knowledgemanager.documents.index', $project) }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Back') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm prose prose-slate max-w-none">
        @include('knowledgemanager::documents.partials.' . $document['partial'])
    </div>
</div>
@endsection
