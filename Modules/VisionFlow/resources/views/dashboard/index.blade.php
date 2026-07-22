@extends('layouts.shell', ['title' => __('VisionFlow Dashboard')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('VisionFlow') }}</h1>
    <div class="flex justify-end">
        <a href="{{ route('visionflow.organizations.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Organization') }}</a>
    </div>
    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No organizations yet.') }}</div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($organizations as $organization)
                <a href="{{ route('visionflow.organizations.show', $organization) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-300">
                    <div class="font-semibold text-slate-900">{{ $organization->name }}</div>
                    <div class="text-sm text-slate-500 mt-2 flex gap-3">
                        <span>{{ $organization->values_count }} {{ __('values') }}</span>
                        <span>{{ $organization->principles_count }} {{ __('principles') }}</span>
                        <span>{{ $organization->missions_count }} {{ __('missions') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
