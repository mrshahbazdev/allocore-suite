@php($title = $project ? __('Edit Project') : __('New Project'))
@extends('layouts.shell')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
        <a href="{{ route('knowledgemanager.projects.index') }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Cancel') }}</a>
    </div>

    <form method="POST" action="{{ $project ? route('knowledgemanager.projects.update', $project) : route('knowledgemanager.projects.store') }}" class="space-y-6">
        @csrf
        @if($project) @method('PUT') @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Project Details') }}</h2>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="project[name]" value="{{ old('project.name', $project?->name) }}" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
                    <input type="text" name="project[slug]" value="{{ old('project.slug', $project?->slug) }}" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="project[status]" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">
                        @foreach(['draft' => __('Draft'), 'published' => __('Published'), 'archived' => __('Archived')] as $value => $label)
                            <option value="{{ $value }}" {{ old('project.status', $project?->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="project[description]" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">{{ old('project.description', $project?->description) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('URL') }}</label>
                    <input type="url" name="project[url]" value="{{ old('project.url', $project?->url) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Industry') }}</label>
                    <input type="text" name="project[industry]" value="{{ old('project.industry', $project?->industry) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Stage') }}</label>
                <input type="text" name="project[stage]" value="{{ old('project.stage', $project?->stage) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#e68200]">{{ $project ? __('Update Project') : __('Create Project') }}</button>
        </div>
    </form>
</div>
@endsection
