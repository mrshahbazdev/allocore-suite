@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Mini-Audit Questions') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Manage the deep questions shown for each pillar in small audits.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($pillars as $pillar)
            <a href="{{ route('admin.audits.pillar-questions.edit', $pillar) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                <div>
                    <h2 class="font-semibold text-slate-900">{{ $pillar }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ floor(($counts[$pillar] ?? 0) / 5) }} {{ __('question groups configured') }}</p>
                </div>
                <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">{{ __('Edit') }}</span>
            </a>
        @endforeach
    </div>
@endsection
