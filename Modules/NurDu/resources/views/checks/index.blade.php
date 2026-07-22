@extends('layouts.shell', ['title' => __('Vision Checks')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Vision Checks') }}</h1>
        <a href="{{ route('nurdu.checks.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Check') }}</a>
    </div>

    @if ($checks->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No vision checks yet.') }}</div>
    @else
        <div class="grid md:grid-cols-2 gap-4">
            @foreach ($checks as $check)
                <a href="{{ route('nurdu.checks.show', $check) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                    <div class="font-semibold text-slate-900">{{ $check->check_date->format('Y-m-d') }}</div>
                    <div class="text-sm text-slate-600 mt-2">{{ $check->actionItems->count() }} {{ __('action items') }}</div>
                    <div class="mt-2 inline-flex rounded-full px-2 py-1 text-xs font-medium bg-slate-100">{{ $check->q1_answer }}</div>
                </a>
            @endforeach
        </div>
        {{ $checks->links() }}
    @endif
</div>
@endsection
