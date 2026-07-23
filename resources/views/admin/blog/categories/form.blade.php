@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $category->exists ? __('Edit category') : __('New category') }}</h1>
    </div>

    <form method="POST" action="{{ $category->exists ? route('admin.blog.categories.update', $category) : route('admin.blog.categories.store') }}" class="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Sort order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    {{ __('Active') }}
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $category->exists ? __('Update') : __('Save') }}</button>
            <a href="{{ route('admin.blog.categories.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
