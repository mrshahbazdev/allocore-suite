@extends('layouts.shell')

@section('content')
    @php($pageTitle = __('Knowledge'))

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Knowledge') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Terms and concepts that power the Allocore Framework.') }}</p>
    </div>

    @forelse ($terms as $category => $group)
        <div class="mb-8">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ $category }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($group as $term)
                    <a href="{{ route('knowledge.show', $term) }}" class="card transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-[#ff9200]">{{ $term->term }}</h3>
                            @if ($term->is_beginner_friendly)
                                <span class="badge badge-green">{{ __('Easy') }}</span>
                            @endif
                        </div>
                        <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $term->simple_definition ?: $term->definition }}</p>
                        <span class="mt-4 inline-flex items-center text-sm font-semibold text-[#0094af] hover:underline">
                            {{ __('Read more') }}
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card text-center text-slate-500">
            <p>{{ __('No knowledge articles yet.') }}</p>
        </div>
    @endforelse
@endsection
