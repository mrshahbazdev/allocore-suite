@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $cluster->name }}</h1>
            <p class="text-sm text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('clusterforge.keywords_count') }} · {{ $cluster->algorithm }} · {{ $cluster->status }}</p>
            @if ($cluster->tags)
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach ($cluster->tags as $tag)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($cluster->is_public)
                <a href="{{ $cluster->shareUrl() }}" target="_blank" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">{{ __('clusterforge.public_link') }}</a>
            @endif
            <a href="{{ route('clusterforge.export', $cluster) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('clusterforge.export') }}</a>
            <a href="{{ route('clusterforge.edit', $cluster) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
            <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('clusterforge.back') }}</a>
        </div>
    </div>

    @if ($cluster->status === 'processing')
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">{{ __('clusterforge.processing_message') }}</div>
    @elseif ($cluster->status === 'failed')
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">{{ $cluster->processing_error }}</div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($cluster->clusters ?? [] as $topic => $keywords)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 font-semibold text-slate-900">{{ $topic }}</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach ($keywords as $keyword)
                        <li class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">{{ $keyword }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endsection
