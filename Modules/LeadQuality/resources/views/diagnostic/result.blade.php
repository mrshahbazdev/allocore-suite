@extends('layouts.shell')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Diagnostic result') }}</h1>
        <div class="mt-4 text-5xl font-semibold text-indigo-600">{{ $score }}</div>
        <div class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $riskClass }}">{{ $risk }}</div>
    </div>
@endsection
