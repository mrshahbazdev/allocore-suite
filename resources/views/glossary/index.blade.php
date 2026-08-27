@extends('layouts.public')

@section('content')
    <div class="bg-slate-900 py-16 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold sm:text-4xl">{{ __('Glossary') }}</h1>
            <p class="mt-2 text-lg text-slate-300">{{ __('Terms and concepts that power the Allocore Framework.') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Alphabet Filter Bar --}}
        @if (! empty($availableLetters))
            <div class="mb-10 flex flex-wrap items-center gap-1 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <a href="{{ route('glossary.index') }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ empty($letter) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    {{ __('All') }}
                </a>
                @foreach (range('A', 'Z') as $l)
                    @php($hasItems = in_array($l, $availableLetters, true))
                    @if ($hasItems)
                        <a href="{{ route('glossary.index', ['letter' => $l]) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold transition {{ $letter === $l ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-600' }}">
                            {{ $l }}
                        </a>
                    @else
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium text-slate-300 cursor-not-allowed">
                            {{ $l }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
            <div class="mb-10">
                <h2 class="mb-4 text-xl font-semibold text-slate-900">{{ $category }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($group as $term)
                        <a href="{{ route('glossary.show', $term) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-indigo-700">{{ $term->term }}</h3>
                                @if ($term->is_beginner_friendly)
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Easy') }}</span>
                                @endif
                            </div>
                            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $term->simple_definition ?: $term->definition }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-center text-slate-500">{{ __('No glossary terms yet.') }}</p>
        @endforelse
    </div>
@endsection
