@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $cluster->name }}</h1>
            <p class="text-sm text-slate-500">{{ count($cluster->keywords ?? []) }} {{ __('clusterforge.keywords_count') }}</p>
        </div>
        <a href="{{ route('clusterforge.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('clusterforge.back') }}</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($cluster->clusters as $topic => $keywords)
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
