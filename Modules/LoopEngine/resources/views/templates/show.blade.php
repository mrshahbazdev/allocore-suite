@extends('layouts.shell')

@section('title', $template->localizedName())
@section('page-title', $template->localizedName())

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $template->localizedName() }}</h1>
                <p class="text-sm text-slate-500">{{ $template->localizedDescription() }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $template->category }} — {{ $template->install_count }} {{ __('installs') }} — {{ number_format($template->rating, 1) }} / 5</div>
            </div>
            <form method="POST" action="{{ route('loopengine.templates.install', $template) }}" class="inline">@csrf<button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Install') }}</button></form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Rate this template') }}</h2>
            <form method="POST" action="{{ route('loopengine.templates.rate', $template) }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Rating') }}</label>
                    <select name="rating" class="mt-1 rounded-lg border-slate-300">
                        @foreach (range(1, 5) as $i)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Review') }}</label><input type="text" name="review" class="mt-1 rounded-lg border-slate-300"></div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Submit') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Reviews') }}</h2>
            @foreach ($template->ratings as $rating)
                <div class="mt-3 border-b border-slate-100 pb-3 last:border-0">
                    <div class="text-sm font-medium">{{ $rating->user->name }} — {{ $rating->rating }}/5</div>
                    <div class="text-sm text-slate-500">{{ $rating->review }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
