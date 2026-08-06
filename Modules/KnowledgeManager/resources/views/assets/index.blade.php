@php($types = ['module' => __('Module'), 'api' => __('API'), 'table' => __('Database Table'), 'dependency' => __('Dependency')])
@extends('layouts.shell')

@section('content')
<div class="space-y-6" x-data="{ assets: [] }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $project->name }} — {{ __('Assets') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Modules, APIs, tables and dependencies discovered in the codebase.') }}</p>
        </div>
        <a href="{{ route('knowledgemanager.projects.show', $project) }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Back') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Assets') }}</h2>
        <form method="POST" action="{{ route('knowledgemanager.assets.store', $project) }}" class="mt-4 space-y-4">
            @csrf
            <template x-for="(asset, index) in assets" :key="index">
                <div class="grid gap-4 rounded-lg border border-slate-100 bg-slate-50 p-4 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">{{ __('Type') }}</label>
                        <select x-bind:name="`assets[${index}][type]`" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">{{ __('Name') }}</label>
                        <input type="text" x-bind:name="`assets[${index}][name]`" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">{{ __('Link') }}</label>
                        <input type="url" x-bind:name="`assets[${index}][link]`" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="button" @click="assets.splice(index, 1)" class="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100">{{ __('Remove') }}</button>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-medium text-slate-700">{{ __('Description') }}</label>
                        <input type="text" x-bind:name="`assets[${index}][description]`" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                </div>
            </template>

            <div class="flex gap-2">
                <button type="button" @click="assets.push({})" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">+ {{ __('Add row') }}</button>
                <button type="submit" x-show="assets.length > 0" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Save Assets') }}</button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Existing Assets') }}</h2>
        @if($project->assets->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No assets recorded yet.') }}</p>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($project->assets as $asset)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-[#0094af] px-2 py-0.5 text-xs font-medium text-white">{{ $types[$asset->type] ?? ucfirst($asset->type) }}</span>
                            <form method="POST" action="{{ route('knowledgemanager.assets.destroy', [$project, $asset]) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Delete') }}</button>
                            </form>
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-slate-900">{{ $asset->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $asset->description }}</p>
                        @if($asset->link)
                            <a href="{{ $asset->link }}" target="_blank" class="mt-2 inline-block text-xs text-[#0094af] hover:underline">{{ __('Open') }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
