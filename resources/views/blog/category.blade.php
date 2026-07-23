@extends('layouts.public')

@section('title', $category->name)
@section('meta_description', $category->description ?: __('Posts in :name', ['name' => $category->name]))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="mt-2 text-slate-600">{{ $category->description }}</p>
        @endif
    </div>

    @if ($posts->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No posts in this category.') }}</div>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    @if ($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="mb-4 h-40 w-full rounded-lg object-cover">
                    @endif
                    <h2 class="text-lg font-bold text-slate-900"><a href="{{ route('blog.show', $post) }}" class="hover:text-indigo-600">{{ $post->title }}</a></h2>
                    <p class="mt-2 text-sm text-slate-600">{{ Str::limit($post->excerpt ?: strip_tags($post->body), 120) }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $post->published_at?->format('M d, Y') }}</p>
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
