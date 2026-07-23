@extends('layouts.public')

@section('title', __('Blog'))
@section('meta_description', __('Latest news, guides and updates from Allocore Suite.'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10 text-center">
        <h1 class="text-4xl font-bold tracking-tight text-slate-900">{{ __('Blog') }}</h1>
        <p class="mt-2 text-lg text-slate-600">{{ __('Latest news, guides and updates.') }}</p>
    </div>

    @if ($featured->isNotEmpty())
        <div class="mb-12 grid gap-6 md:grid-cols-3">
            @foreach ($featured as $post)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    @if ($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="mb-4 h-40 w-full rounded-lg object-cover">
                    @endif
                    <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $post->category->name ?? '' }}</span>
                    <h2 class="mt-2 text-xl font-bold text-slate-900">
                        <a href="{{ route('blog.show', $post) }}" class="hover:text-indigo-600">{{ $post->title }}</a>
                    </h2>
                    <p class="mt-2 text-sm text-slate-600">{{ Str::limit($post->excerpt ?: strip_tags($post->body), 120) }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-4">
        <div class="lg:col-span-3">
            <form method="GET" action="{{ route('blog.index') }}" class="mb-6">
                <div class="flex gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search articles...') }}" class="flex-1 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
                </div>
            </form>

            @if ($posts->isEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No posts found.') }}</div>
            @else
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($posts as $post)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            @if ($post->featured_image)
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="mb-4 h-48 w-full rounded-lg object-cover">
                            @endif
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span class="font-semibold text-indigo-600">{{ $post->category->name ?? '' }}</span>
                                <span>·</span>
                                <span>{{ $post->published_at?->format('M d, Y') }}</span>
                                <span>·</span>
                                <span>{{ $post->readingTime() }} min read</span>
                            </div>
                            <h2 class="mt-2 text-xl font-bold text-slate-900">
                                <a href="{{ route('blog.show', $post) }}" class="hover:text-indigo-600">{{ $post->title }}</a>
                            </h2>
                            <p class="mt-2 text-sm text-slate-600">{{ Str::limit($post->excerpt ?: strip_tags($post->body), 160) }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($post->tags as $tag)
                                    <a href="{{ route('blog.tag', $tag) }}" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-200">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-8">{{ $posts->links() }}</div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-slate-900">{{ __('Categories') }}</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($categories as $category)
                        <li><a href="{{ route('blog.category', $category) }}" class="text-slate-600 hover:text-indigo-600">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-slate-900">{{ __('Tags') }}</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <a href="{{ route('blog.tag', $tag) }}" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-200">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('blog.feed') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('RSS Feed') }}</a>
        </aside>
    </div>
</div>
@endsection
