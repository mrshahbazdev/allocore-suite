@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Blog Posts') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Create and manage blog posts with SEO.') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.categories.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Categories') }}</a>
            <a href="{{ route('admin.blog.tags.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Tags') }}</a>
            <a href="{{ route('admin.blog.comments.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Comments') }}</a>
            <a href="{{ route('admin.blog.posts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New post') }}</a>
        </div>
    </div>

    @if ($posts->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No posts yet.') }}</div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('Title') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Category') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Published') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Views') }}</th>
                        <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($posts as $post)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $post->title }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $post->category->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                @if ($post->isPublished())
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Published') }}</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ __('Draft') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $post->published_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $post->views }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.blog.posts.edit', $post) }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Edit') }}</a>
                                <a href="{{ route('blog.show', $post) }}" target="_blank" class="ml-3 text-sm text-indigo-600 hover:text-indigo-500">{{ __('View') }}</a>
                                <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}" class="ml-3 inline" onsubmit="return confirm('{{ __('Delete this post?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $posts->links() }}</div>
        </div>
    @endif
@endsection
