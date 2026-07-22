@extends('layouts.shell')

@section('title', __('Review'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ ucfirst($review->review_type) }} {{ __('Review') }}</h1>
        <form method="POST" action="{{ route('cashcore.behavior.complete', $review) }}" class="mt-6 space-y-3">
            @csrf
            @foreach ($review->checklist ?? [] as $index => $item)
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                    <input type="checkbox" name="checklist[{{ $index }}][done]" value="1" {{ $item['done'] ? 'checked' : '' }} class="rounded border-slate-300">
                    <input type="hidden" name="checklist[{{ $index }}][task]" value="{{ $item['task'] }}">
                    <span class="text-sm text-slate-700">{{ $item['task'] }}</span>
                </label>
            @endforeach
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Complete Review') }}</button>
        </form>
    </div>
@endsection
