@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('search.title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('search.description') }}</p>
    </div>

    <form method="GET" action="{{ route('search') }}" class="mb-6">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $query }}" placeholder="{{ __('search.placeholder') }}" class="flex-1 rounded-lg border-slate-300 px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('search.title') }}</button>
        </div>
    </form>

    @if ($query)
        @forelse ($results as $group => $items)
            <div class="mb-6">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</h2>
                <div class="space-y-2">
                    @foreach ($items as $item)
                        <a href="{{ $item['url'] ?? '#' }}" class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300">
                            <div class="font-medium text-slate-900">{{ $item['title'] }}</div>
                            <div class="text-xs text-slate-400">{{ $item['type'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('search.no_results') }} “{{ $query }}”.</div>
        @endforelse
    @endif
@endsection
