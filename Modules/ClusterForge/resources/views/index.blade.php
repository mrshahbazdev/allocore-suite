@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('clusterforge.name') }}</h1>
        <p class="text-sm text-slate-500">{{ __('clusterforge.description') }}</p>
    </div>

    <form method="POST" action="{{ route('clusterforge.store') }}" enctype="multipart/form-data" class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.project_name') }}</label>
            <input type="text" name="name" required class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('e.g. SaaS SEO') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.description_label') }}</label>
            <textarea name="description" rows="2" class="mt-2 w-full rounded-lg border-slate-300 text-sm"></textarea>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.algorithm') }}</label>
                <select name="algorithm" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                    <option value="terms">{{ __('clusterforge.terms_algorithm') }}</option>
                    <option value="similarity">{{ __('clusterforge.similarity_algorithm') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.tags') }}</label>
                <input type="text" name="tags" class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="seo, content">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.keywords_file') }}</label>
                <input type="file" name="keywords_file" accept=".csv,.txt" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.keywords') }}</label>
            <textarea name="keywords" rows="6" class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('clusterforge.keywords_placeholder') }}"></textarea>
            <p class="mt-1 text-xs text-slate-500">{{ __('clusterforge.or_upload_csv') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_public" id="is_public" value="1" class="rounded border-slate-300 text-indigo-600">
            <label for="is_public" class="text-sm text-slate-700">{{ __('clusterforge.make_public') }}</label>
        </div>
        <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('clusterforge.generate') }}</button>
    </form>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('clusterforge.recent_projects') }}</h2>
        <form method="GET" action="{{ route('clusterforge.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('clusterforge.search') }}" class="rounded-lg border-slate-300 text-sm">
            <select name="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('clusterforge.all_statuses') }}</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('clusterforge.status_processing') }}</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('clusterforge.status_completed') }}</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('clusterforge.status_failed') }}</option>
            </select>
            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Filter') }}</button>
        </form>
    </div>

    @if ($clusters->isNotEmpty())
        <div class="space-y-3">
            @foreach ($clusters as $cluster)
                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="font-medium text-slate-900">{{ $cluster->name }}</div>
                        <div class="text-xs text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('clusterforge.keywords_count') }} · {{ count($cluster->clusters ?? []) }} {{ __('clusterforge.clusters_count') }} · {{ $cluster->algorithm }} · {{ $cluster->status }}</div>
                        @if ($cluster->tags)
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($cluster->tags as $tag)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($cluster->is_public)
                            <a href="{{ $cluster->shareUrl() }}" target="_blank" class="text-xs text-indigo-600 hover:underline">{{ __('clusterforge.public_link') }}</a>
                        @endif
                        <a href="{{ route('clusterforge.show', $cluster) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                        <a href="{{ route('clusterforge.edit', $cluster) }}" class="text-sm text-slate-600 hover:underline">{{ __('Edit') }}</a>
                        <a href="{{ route('clusterforge.export', $cluster) }}" class="text-sm text-slate-600 hover:underline">{{ __('clusterforge.export') }}</a>
                        <form method="POST" action="{{ route('clusterforge.destroy', $cluster) }}" onsubmit="return confirm('{{ __('clusterforge.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $clusters->links() }}</div>
    @endif
@endsection
