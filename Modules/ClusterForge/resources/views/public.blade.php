@extends('layouts.guest')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900">{{ $cluster->name }}</h1>
            @if ($cluster->description)
                <p class="mt-2 text-slate-600">{{ $cluster->description }}</p>
            @endif
            <p class="mt-1 text-sm text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('clusterforge.keywords_count') }} · {{ count($cluster->clusters ?? []) }} {{ __('clusterforge.clusters_count') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
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
    </div>
@endsection
