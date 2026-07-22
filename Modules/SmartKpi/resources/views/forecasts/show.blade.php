@extends('layouts.shell')

@section('title', $forecast->kpiDefinition->localizedName().' '.__('Forecast'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $forecast->kpiDefinition->localizedName() }}</h1>
        <p class="text-sm text-slate-500">{{ $forecast->method }} {{ $forecast->horizon }}</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-xs uppercase text-slate-500">{{ __('Forecast') }}</div><div class="text-xl font-bold">{{ $forecast->value ?? '-' }}</div></div>
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-xs uppercase text-slate-500">{{ __('Confidence Lower') }}</div><div class="text-xl font-bold">{{ $forecast->confidence_lower ?? '-' }}</div></div>
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-xs uppercase text-slate-500">{{ __('Confidence Upper') }}</div><div class="text-xl font-bold">{{ $forecast->confidence_upper ?? '-' }}</div></div>
        </div>
    </div>
@endsection
