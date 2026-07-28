@extends('layouts.shell')

@section('title', __('Clusters'))
@section('page-title', __('Clusters'))

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('ClusterForge') }}</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Keyword Clusters') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Group keywords by semantic similarity or shared terms.') }}</p>
            </div>
            <a href="#create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('New Cluster') }}</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('clusterforge.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Total') }}</div><svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            </a>
            <a href="{{ route('clusterforge.index', ['status' => 'processing']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Processing') }}</div><svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['processing'] }}</div>
            </a>
            <a href="{{ route('clusterforge.index', ['status' => 'completed']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Completed') }}</div><svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['completed'] }}</div>
            </a>
            <a href="{{ route('clusterforge.index', ['status' => 'failed']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-rose-300">
                <div class="flex items-center justify-between"><div class="text-xs uppercase text-slate-500">{{ __('Failed') }}</div><svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg></div>
                <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['failed'] }}</div>
            </a>
        </div>

        <div id="create" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('New Cluster Project') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Paste keywords or upload a CSV/TXT file to group them automatically.') }}</p>
            <form method="POST" action="{{ route('clusterforge.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Project Name') }}</label>
                    <input type="text" name="name" required class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('e.g. SaaS SEO') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Algorithm') }}</label>
                        <select name="algorithm" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="terms">{{ __('Shared Terms') }}</option>
                            <option value="similarity">{{ __('Semantic Similarity') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Tags') }}</label>
                        <input type="text" name="tags" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="seo, content">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Keywords File') }}</label>
                        <input type="file" name="keywords_file" accept=".csv,.txt" class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Keywords') }}</label>
                    <textarea name="keywords" rows="6" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Enter one keyword per line or comma-separated') }}"></textarea>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Or upload a .csv / .txt file above.') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" id="is_public" value="1" class="rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_public" class="text-sm text-slate-700">{{ __('Make public and shareable') }}</label>
                </div>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Generate Clusters') }}</button>
            </form>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Projects') }}</h2>
            <form method="GET" action="{{ route('clusterforge.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search') }}" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="status" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                </select>
                <button class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Filter') }}</button>
            </form>
        </div>

        @if ($clusters->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('No clusters yet. Create one above.') }}
            </div>
        @else
            <div class="space-y-3">
                @foreach ($clusters as $cluster)
                    @php($statusClass = match($cluster->status) {
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'processing' => 'bg-amber-100 text-amber-700',
                        'failed' => 'bg-rose-100 text-rose-700',
                        default => 'bg-slate-100 text-slate-700',
                    })
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900">{{ $cluster->name }}</div>
                            <div class="text-xs text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('keywords') }} · {{ count($cluster->clusters ?? []) }} {{ __('clusters') }} · {{ $cluster->algorithm }} · <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ __(ucfirst($cluster->status)) }}</span></div>
                            @if ($cluster->tags)
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach ($cluster->tags as $tag)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($cluster->is_public)
                                <a href="{{ $cluster->shareUrl() }}" target="_blank" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">{{ __('Public Link') }}</a>
                            @endif
                            <a href="{{ route('clusterforge.show', $cluster) }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('View') }}</a>
                            <a href="{{ route('clusterforge.edit', $cluster) }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                            <a href="{{ route('clusterforge.export', $cluster) }}" class="rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Export CSV') }}</a>
                            <form method="POST" action="{{ route('clusterforge.destroy', $cluster) }}" onsubmit="return confirm('{{ __('Delete this cluster?') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-100">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $clusters->links() }}</div>
        @endif
    </div>
@endsection
