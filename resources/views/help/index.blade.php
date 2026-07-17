@extends('layouts.guest')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-16">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('Help Center') }}</h1>
        <p class="mt-2 text-slate-600">{{ __('Browse support articles and guides.') }}</p>

        @if ($articles->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                {{ __('No help articles yet.') }}
            </div>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ($articles as $article)
                    @php($translation = $article['translation'])
                    <a href="{{ route('page.show', $translation->slug) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                        <h2 class="font-semibold text-slate-900">{{ $translation->title }}</h2>
                        @if ($translation->meta_description)
                            <p class="mt-2 text-sm text-slate-600 line-clamp-2">{{ $translation->meta_description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
