@extends('layouts.shell')

@section('title', $cluster->name)
@section('page-title', $cluster->name)

@section('content')
    @php($statusClass = match($cluster->status) {
        'completed' => 'bg-emerald-100 text-emerald-700',
        'processing' => 'bg-amber-100 text-amber-700',
        'failed' => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-100 text-slate-700',
    })

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('ClusterForge') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ $cluster->name }}</h1>
                <p class="text-sm text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('keywords') }} · {{ $cluster->algorithm }} · <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ __(ucfirst($cluster->status)) }}</span></p>
                @if ($cluster->tags)
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($cluster->tags as $tag)
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($cluster->is_public)
                    <a href="{{ $cluster->shareUrl() }}" target="_blank" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">{{ __('Public Link') }}</a>
                @endif
                <a href="{{ route('clusterforge.export', $cluster) }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Export CSV') }}</a>
                <a href="{{ route('clusterforge.edit', $cluster) }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Back') }}</a>
            </div>
        </div>

        @if ($cluster->status === 'processing')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800">{{ __('clusterforge.processing_message') }}</div>
        @elseif ($cluster->status === 'failed')
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800">{{ $cluster->processing_error }}</div>
        @endif

        @if (empty($cluster->clusters))
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('No clusters generated yet.') }}
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($cluster->clusters as $topic => $keywords)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 font-semibold text-slate-900">{{ $topic }}</h2>
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($keywords as $keyword)
                                <li class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">{{ $keyword }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
