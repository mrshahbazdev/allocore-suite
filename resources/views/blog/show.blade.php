@extends('layouts.public')

@section('title', $post->effectiveMetaTitle())
@section('meta_description', $post->effectiveMetaDescription())
@section('meta_keywords', $post->meta_keywords ?? '')
@section('og_title', $post->effectiveOgTitle())
@section('og_description', $post->effectiveOgDescription())
@section('og_image', $post->effectiveOgImage() ?? '')

@push('meta')
    <link rel="canonical" href="{{ route('blog.show', $post) }}">
    @php($schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $post->effectiveMetaDescription(),
        'image' => $post->effectiveOgImage(),
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => $post->updated_at->toIso8601String(),
        'author' => ['@type' => 'Person', 'name' => $post->user?->name ?? config('app.name')],
        'publisher' => ['@type' => 'Organization', 'name' => \App\Models\SiteSetting::value('site_name', config('app.name'))],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post)],
    ])
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('blog.category', $post->category) }}" class="font-semibold text-indigo-600 hover:underline">{{ $post->category?->name ?? '' }}</a>
            <span>·</span>
            <span>{{ $post->published_at?->format('M d, Y') }}</span>
            <span>·</span>
            <span>{{ $post->readingTime() }} min read</span>
        </div>

        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ $post->title }}</h1>

        @if ($post->featured_image)
            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="mt-6 w-full rounded-2xl object-cover">
        @endif

        <div class="prose prose-slate mt-8 max-w-none">
            {!! $post->body !!}
        </div>

        <div class="mt-8 flex flex-wrap gap-2">
            @foreach ($post->tags as $tag)
                <a href="{{ route('blog.tag', $tag) }}" class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600 hover:bg-slate-200">#{{ $tag->name }}</a>
            @endforeach
        </div>
    </article>

    @if ($related->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-2xl font-bold text-slate-900">{{ __('Related posts') }}</h2>
            <div class="mt-4 grid gap-6 md:grid-cols-3">
                @foreach ($related as $r)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-900"><a href="{{ route('blog.show', $r) }}" class="hover:text-indigo-600">{{ $r->title }}</a></h3>
                        <p class="mt-2 text-sm text-slate-600">{{ Str::limit($r->excerpt ?: strip_tags($r->body), 100) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900">{{ __('Comments') }} ({{ $comments->count() }})</h2>

        <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="mt-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <input type="text" name="name" placeholder="{{ __('Name') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <input type="email" name="email" placeholder="{{ __('Email') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
            <input type="url" name="website" placeholder="{{ __('Website (optional)') }}" class="mt-3 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <textarea name="body" rows="3" placeholder="{{ __('Write a comment...') }}" class="mt-3 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
            <button type="submit" class="mt-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Submit comment') }}</button>
        </form>

        <div class="mt-6 space-y-4">
            @foreach ($comments as $comment)
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-900">{{ $comment->name }}</span>
                        <span class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-700">{{ $comment->body }}</p>
                    @if ($comment->approvedReplies->isNotEmpty())
                        <div class="mt-3 space-y-3 pl-4">
                            @foreach ($comment->approvedReplies as $reply)
                                <div class="rounded-lg border border-slate-100 bg-white p-3">
                                    <span class="text-sm font-semibold text-slate-900">{{ $reply->name }}</span>
                                    <p class="text-sm text-slate-700">{{ $reply->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
