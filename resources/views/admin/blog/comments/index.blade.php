@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Blog Comments') }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.comments.index') }}" class="rounded-lg {{ request('status') !== 'approved' && request('status') !== 'pending' ? 'bg-indigo-600 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }} px-4 py-2 text-sm font-semibold">{{ __('All') }}</a>
            <a href="{{ route('admin.blog.comments.index', ['status' => 'pending']) }}" class="rounded-lg {{ request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }} px-4 py-2 text-sm font-semibold">{{ __('Pending') }}</a>
            <a href="{{ route('admin.blog.comments.index', ['status' => 'approved']) }}" class="rounded-lg {{ request('status') === 'approved' ? 'bg-indigo-600 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }} px-4 py-2 text-sm font-semibold">{{ __('Approved') }}</a>
        </div>
    </div>

    @if ($comments->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No comments yet.') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($comments as $comment)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $comment->name }} <span class="font-normal text-slate-500">&lt;{{ $comment->email }}&gt;</span></h3>
                            <p class="text-xs text-slate-500">{{ __('On') }} <a href="{{ route('blog.show', $comment->post) }}" class="text-indigo-600 hover:underline">{{ $comment->post->title }}</a> · {{ $comment->created_at->diffForHumans() }}</p>
                            <p class="mt-2 text-sm text-slate-700">{{ $comment->body }}</p>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $comment->is_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $comment->is_approved ? __('Approved') : __('Pending') }}</span>
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        @if (! $comment->is_approved)
                            <form method="POST" action="{{ route('admin.blog.comments.approve', $comment) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-800">{{ __('Approve') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.blog.comments.destroy', $comment) }}" onsubmit="return confirm('{{ __('Delete this comment?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $comments->links() }}</div>
    @endif
@endsection
