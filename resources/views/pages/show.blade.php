@extends('layouts.public')

@section('title', $translation->meta_title ?: $translation->title)
@section('meta_description', $translation->meta_description ?? '')
@section('meta_keywords', $translation->meta_keywords ?? '')
@section('og_title', $translation->og_title ?: $translation->title)
@section('og_description', $translation->og_description ?? '')
@section('og_image', $translation->og_image ?? '')

@push('meta')
    @foreach ($alternates as $locale => $url)
        <link rel="alternate" hreflang="{{ str_replace('_', '-', $locale) }}" href="{{ $url }}">
    @endforeach
@endpush

@section('content')
    <section class="border-b border-slate-200 bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">{{ $translation->title }}</h1>
        </div>
    </section>

    <section class="py-12 lg:py-20">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <article class="prose prose-slate max-w-none">
                {!! $translation->body !!}
            </article>
        </div>
    </section>
@endsection
