@extends('layouts.shell')

@section('title', $run->process->localizedName())
@section('page-title', $run->process->localizedName())

@section('content')
    <div class="space-y-6 max-w-2xl">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $run->process->localizedName() }}</h1>
                <p class="text-sm text-slate-500">{{ __('Step') }} {{ $run->currentStep?->order }} / {{ $run->process->steps->count() }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $run->status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">{{ __($run->status) }}</span>
        </div>

        @if ($run->currentStep)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-slate-900">{{ $run->currentStep->localizedQuestion() }}</h2>
                @if ($run->currentStep->localizedHelpText())
                    <p class="mt-2 text-sm text-slate-500">{{ $run->currentStep->localizedHelpText() }}</p>
                @endif

                <form method="POST" action="{{ route('loopengine.runs.answer', $run) }}" class="mt-6 space-y-4">
                    @csrf
                    @if ($run->currentStep->options->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($run->currentStep->options as $option)
                                <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:border-indigo-300 cursor-pointer">
                                    <input type="radio" name="option_id" value="{{ $option->id }}" required class="rounded-full border-slate-300">
                                    <span class="font-medium">{{ $option->localizedLabel() }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <textarea name="response_text" rows="4" class="w-full rounded-lg border-slate-300" placeholder="{{ __('Your response...') }}"></textarea>
                    @endif

                    <div class="flex gap-2">
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Continue') }}</button>
                        <form method="POST" action="{{ route('loopengine.runs.pause', $run) }}" class="inline">@csrf<button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Pause') }}</button></form>
                        <form method="POST" action="{{ route('loopengine.runs.cancel', $run) }}" class="inline">@csrf<button class="rounded-lg bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">{{ __('Cancel') }}</button></form>
                    </div>
                </form>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center">
                <p class="text-slate-600">{{ __('No current step.') }}</p>
                <a href="{{ route('loopengine.runs.summary', $run) }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('View Summary') }}</a>
            </div>
        @endif
    </div>
@endsection
