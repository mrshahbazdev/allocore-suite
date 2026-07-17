@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('clusterforge.name') }}</h1>
        <p class="text-sm text-slate-500">{{ __('clusterforge.description') }}</p>
    </div>

    <form method="POST" action="{{ route('clusterforge.store') }}" class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.project_name') }}</label>
            <input type="text" name="name" required class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('e.g. SaaS SEO') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('clusterforge.keywords') }}</label>
            <textarea name="keywords" rows="8" required class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('clusterforge.keywords_placeholder') }}"></textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_public" id="is_public" value="1" class="rounded border-slate-300 text-indigo-600">
            <label for="is_public" class="text-sm text-slate-700">{{ __('clusterforge.make_public') }}</label>
        </div>
        <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('clusterforge.generate') }}</button>
    </form>

    @if ($clusters->isNotEmpty())
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('clusterforge.recent_projects') }}</h2>
        <div class="space-y-3">
            @foreach ($clusters as $cluster)
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div>
                        <div class="font-medium text-slate-900">{{ $cluster->name }}</div>
                        <div class="text-xs text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('clusterforge.keywords_count') }} · {{ count($cluster->clusters ?? []) }} {{ __('clusterforge.clusters_count') }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('clusterforge.show', $cluster) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
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
