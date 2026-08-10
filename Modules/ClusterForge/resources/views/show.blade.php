@extends('layouts.shell')

@php($title = $project->topic)
@section('page-title', __('ClusterForge'))

@section('topbar-actions')
    <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back') }}</a>
@endsection

@section('content')
    @php($statusClass = match($project->status) {
        'completed' => 'bg-emerald-100 text-emerald-700',
        'failed' => 'bg-rose-100 text-rose-700',
        default => 'bg-amber-100 text-amber-700',
    })

    <div class="max-w-7xl mx-auto space-y-6" x-data="{ tab: 'pillar' }">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('ClusterForge') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ $project->topic }}</h1>
                <p class="text-sm text-slate-500">{{ $project->website }} · <span id="status-badge" class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $project->statusLabel() }}</span> · <span id="progress-text">{{ $project->progressPercent() }}%</span></p>
                @if ($project->isInProgress())
                    <div class="mt-3 h-2 w-full max-w-md overflow-hidden rounded-full bg-slate-100">
                        <div id="progress-bar" class="h-2 rounded-full bg-indigo-600 transition-all" style="width: {{ $project->progressPercent() }}%"></div>
                    </div>
                @endif
                @if ($project->status === 'failed' && $project->error)
                    <div id="error-box" class="mt-3 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $project->error }}</div>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($project->status === 'completed')
                    <a href="{{ route('clusterforge.export.pillar', $project) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Download pillar .md') }}</a>
                @endif
                @if ($project->status === 'failed')
                    <form method="POST" action="{{ route('clusterforge.retry', $project) }}" class="inline">
                        @csrf
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Retry') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('clusterforge.destroy', $project) }}" onsubmit="return confirm('{{ __('Delete this cluster?') }}')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        @if ($project->isInProgress())
            <div id="processing-message" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
                {{ __('Your cluster is being generated. This page will update automatically.') }}
            </div>
        @endif

        @if ($project->status === 'completed')
            <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                <div class="flex gap-2 overflow-x-auto border-b border-slate-200 pb-2">
                    <button type="button" @click="tab = 'pillar'" :class="tab === 'pillar' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="rounded-lg px-4 py-2 text-sm font-semibold whitespace-nowrap">{{ __('Pillar page') }}</button>
                    @foreach ($project->subtopics as $i => $subtopic)
                        <button type="button" @click="tab = 'subtopic-{{ $subtopic->id }}'" :class="tab === 'subtopic-{{ $subtopic->id }}' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="rounded-lg px-4 py-2 text-sm font-semibold whitespace-nowrap">{{ $subtopic->title }}</button>
                    @endforeach
                </div>

                <div class="p-4">
                    <div x-show="tab === 'pillar'" x-cloak>
                        @if ($project->pillar_title)
                            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">{{ $project->pillar_title }}</h2>
                                    <p class="text-sm text-slate-500">{{ $project->pillar_meta_description }}</p>
                                </div>
                                <a href="{{ route('clusterforge.export.pillar', $project) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Download .md') }}</a>
                            </div>
                            <div class="prose max-w-none prose-slate prose-headings:text-slate-900">
                                {!! Illuminate\Support\Str::markdown($project->pillar_content ?? '') !!}
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">{{ __('No pillar content yet.') }}</div>
                        @endif
                    </div>

                    @foreach ($project->subtopics as $subtopic)
                        <div x-show="tab === 'subtopic-{{ $subtopic->id }}'" x-cloak>
                            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">{{ $subtopic->cluster_title ?: $subtopic->title }}</h2>
                                    <p class="text-sm text-slate-500">{{ $subtopic->cluster_meta_description }}</p>
                                    <p class="text-xs text-slate-400">{{ __('Long-tail keyword') }}: {{ $subtopic->long_tail_keyword }}</p>
                                    @if ($subtopic->search_volume)
                                        <p class="text-xs text-slate-400">{{ __('Search volume') }}: {{ number_format($subtopic->search_volume) }} · CPC: {{ $subtopic->cpc }} · {{ __('Competition') }}: {{ $subtopic->competition }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('clusterforge.export.cluster', [$project, $subtopic]) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Download .md') }}</a>
                            </div>
                            @if ($subtopic->cluster_content)
                                <div class="prose max-w-none prose-slate prose-headings:text-slate-900">
                                    {!! Illuminate\Support\Str::markdown($subtopic->cluster_content) !!}
                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">{{ __('No cluster content yet.') }}</div>
                            @endif

                            <div class="mt-8 border-t border-slate-200 pt-6">
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Questions & Answers') }}</h3>
                                <div class="mt-3 space-y-3">
                                    @foreach ($subtopic->questions as $q)
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="font-medium text-slate-900">{{ $q->question }}</div>
                                            <div class="mt-1 text-sm text-slate-700">{{ $q->answer ?: __('No answer generated.') }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($project->isInProgress() || $project->status === 'failed')
        @push('scripts')
            <script>
                (function () {
                    const statusUrl = '{{ route('clusterforge.status', $project) }}';
                    const projectUrl = '{{ route('clusterforge.show', $project) }}';
                    let wasInProgress = {{ $project->isInProgress() ? 'true' : 'false' }};

                    function poll() {
                        fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => {
                                const badge = document.getElementById('status-badge');
                                const bar = document.getElementById('progress-bar');
                                const text = document.getElementById('progress-text');

                                if (badge) badge.textContent = data.status_label;
                                if (bar) bar.style.width = data.progress_percent + '%';
                                if (text) text.textContent = data.progress_percent + '%';

                                if (data.is_in_progress && ! wasInProgress) {
                                    window.location.href = projectUrl;
                                    return;
                                }

                                if (! data.is_in_progress) {
                                    window.location.href = projectUrl;
                                    return;
                                }

                                setTimeout(poll, 3000);
                            })
                            .catch(() => setTimeout(poll, 5000));
                    }

                    poll();
                })();
            </script>
        @endpush
    @endif
@endsection
