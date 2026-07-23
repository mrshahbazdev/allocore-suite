@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $post->exists ? __('Edit post') : __('New post') }}</h1>
    </div>

    <form method="POST" action="{{ $post->exists ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($post->exists) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
            <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                <select name="category_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Publish date') }}</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Excerpt') }}</label>
            <textarea name="excerpt" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('excerpt', $post->excerpt) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Body') }}</label>
            <textarea name="body" rows="10" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body', $post->body) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Featured image URL') }}</label>
            <input type="url" name="featured_image" value="{{ old('featured_image', $post->featured_image) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Tags') }}</label>
            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                @foreach ($tags as $tag)
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $post->tags->pluck('id')->toArray())) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $tag->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <h3 class="mb-3 font-semibold text-slate-900">{{ __('SEO / Open Graph') }}</h3>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('Meta title') }}</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('Meta description') }}</label>
                <textarea name="meta_description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('meta_description', $post->meta_description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('Meta keywords') }}</label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('OG title') }}</label>
                <input type="text" name="og_title" value="{{ old('og_title', $post->og_title) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('OG description') }}</label>
                <textarea name="og_description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('og_description', $post->og_description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('OG image') }}</label>
                <input type="url" name="og_image" value="{{ old('og_image', $post->og_image) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-6 flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                {{ __('Published') }}
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                {{ __('Featured') }}
            </label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $post->exists ? __('Update') : __('Save') }}</button>
            <a href="{{ route('admin.blog.posts.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
