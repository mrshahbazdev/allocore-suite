@extends('layouts.shell')

@section('title', __('Projects'))
@section('page-title', __('ClusterForge'))

@section('topbar-actions')
    <a href="{{ route('clusterforge.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('New cluster') }}</a>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('ClusterForge') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Topic Clusters') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Turn one topic into a full SEO content cluster with AI.') }}</p>
            </div>
            <a href="{{ route('clusterforge.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('New cluster') }}</a>
        </div>

        @if (! $geminiConfigured)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                {{ __('Gemini API key is not configured. Set GEMINI_API_KEY in your .env file to generate clusters.') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Total') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Processing') }}</div><svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['processing'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['completed'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-rose-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Failed') }}</div><svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['failed'] }}</div>
            </div>
        </div>

        @if ($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('No clusters yet. Create your first cluster to get started.') }}
            </div>
        @else
            <div class="space-y-3">
                @foreach ($projects as $project)
                    @php($statusClass = match($project->status) {
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'failed' => 'bg-rose-100 text-rose-700',
                        default => 'bg-amber-100 text-amber-700',
                    })
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-slate-900">{{ $project->topic }}</div>
                            <div class="text-xs text-slate-500">{{ $project->website }} · {{ $project->created_at?->diffForHumans() }} · <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $project->statusLabel() }}</span></div>
                            @if ($project->isInProgress())
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-indigo-600 transition-all" style="width: {{ $project->progressPercent() }}%"></div>
                                </div>
                            @endif
                            @if ($project->status === 'failed' && $project->error)
                                <div class="mt-1 text-xs text-rose-600 truncate">{{ Str::limit($project->error, 120) }}</div>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('clusterforge.show', $project) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('View') }}</a>
                            @if ($project->status === 'completed')
                                <a href="{{ route('clusterforge.export.pillar', $project) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Pillar .md') }}</a>
                            @endif
                            @if ($project->status === 'failed')
                                <form method="POST" action="{{ route('clusterforge.retry', $project) }}" class="inline">
                                    @csrf
                                    <button class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100">{{ __('Retry') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('clusterforge.destroy', $project) }}" onsubmit="return confirm('{{ __('Delete this cluster?') }}')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-100">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $projects->links() }}</div>
        @endif
    </div>
@endsection
