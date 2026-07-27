@extends('layouts.public')

@section('content')
    <div class="bg-slate-900 py-16 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold sm:text-4xl">{{ __('Glossary') }}</h1>
            <p class="mt-2 text-lg text-slate-300">{{ __('Terms and concepts that power the Allocore Framework.') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @forelse ($terms as $category => $group)
            <div class="mb-10">
                <h2 class="mb-4 text-xl font-semibold text-slate-900">{{ $category }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($group as $term)
                        <a href="{{ route('glossary.show', $term) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                            <h3 class="font-semibold text-indigo-700">{{ $term->term }}</h3>
                            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $term->definition }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-center text-slate-500">{{ __('No glossary terms yet.') }}</p>
        @endforelse
    </div>
@endsection
