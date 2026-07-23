@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $tag->exists ? __('Edit tag') : __('New tag') }}</h1>
    </div>

    <form method="POST" action="{{ $tag->exists ? route('admin.blog.tags.update', $tag) : route('admin.blog.tags.store') }}" class="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($tag->exists) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $tag->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
            <input type="text" name="slug" value="{{ old('slug', $tag->slug) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $tag->exists ? __('Update') : __('Save') }}</button>
            <a href="{{ route('admin.blog.tags.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
