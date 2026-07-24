@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit dashboard') }}</h1>

        <form method="POST" action="{{ route('dashboards.update', $dashboard) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title', $dashboard->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $dashboard->is_default) ? 'checked' : '' }} id="is_default" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_default" class="text-sm text-slate-700">{{ __('Make default') }}</label>
            </div>

            @php($initialWidgets = old('widgets') ? json_decode(old('widgets'), true) : ($dashboard->widgets ?? []))

            <div x-data="{ widgets: @json($initialWidgets), dragIndex: null }" x-init="$watch('widgets', value => $refs.widgetsInput.value = JSON.stringify(value)); $refs.widgetsInput.value = JSON.stringify(widgets)">
                <div class="mb-2 flex items-center justify-between">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Widgets') }}</label>
                    <button type="button" @click="widgets.push({ type: 'stats', title: 'Stats', settings: {} })" class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Add widget') }}</button>
                </div>

                <input type="hidden" name="widgets" x-ref="widgetsInput">

                <div class="space-y-3">
                    <template x-for="(widget, index) in widgets" :key="index">
                        <div draggable="true"
                             @dragstart="dragIndex = index"
                             @dragover.prevent
                             @drop="if (dragIndex !== null && dragIndex !== index) { const item = widgets.splice(dragIndex, 1)[0]; widgets.splice(index, 0, item); dragIndex = null; }"
                             class="cursor-move rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                                <div class="sm:col-span-5">
                                    <label class="block text-xs font-medium text-slate-500">{{ __('Title') }}</label>
                                    <input type="text" x-model="widget.title" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" placeholder="{{ __('Widget title') }}">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-xs font-medium text-slate-500">{{ __('Type') }}</label>
                                    <select x-model="widget.type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                        <option value="stats">{{ __('Stats') }}</option>
                                        <option value="activity">{{ __('Recent activity') }}</option>
                                        <option value="module_usage">{{ __('Module usage chart') }}</option>
                                        <option value="my_tools">{{ __('My tools') }}</option>
                                        <option value="module">{{ __('Module widget') }}</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2" x-show="widget.type === 'module'">
                                    <label class="block text-xs font-medium text-slate-500">{{ __('Module') }}</label>
                                    <select x-model="widget.settings.module_key" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                        @foreach ($availableModules as $key => $name)
                                            <option value="{{ $key }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end justify-end sm:col-span-1">
                                    <button type="button" @click="widgets.splice(index, 1)" class="text-sm text-rose-600 hover:text-rose-700">{{ __('Remove') }}</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <p class="mt-2 text-xs text-slate-500">{{ __('Drag a widget by its card to reorder. The order is saved with the dashboard.') }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('dashboards.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
