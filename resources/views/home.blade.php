@extends('layouts.public')

@section('title', \App\Models\SiteSetting::value('site_name', config('app.name', 'Allocore Suite')))

@section('content')
    @foreach ($blocks as $block)
        @if ($block['enabled'] ?? true)
            @include('blocks.'.$block['type'], ['block' => $block])
        @endif
    @endforeach
@endsection
