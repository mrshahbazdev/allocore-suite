@extends('layouts.shell')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">{{ $sequence->name }}</h1>
                        <p class="text-sm text-slate-500">{{ __('Sequence builder') }}</p>
                    </div>
                    <div class="text-sm {{ $sequence->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $sequence->is_active ? __('Active') : __('Paused') }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Steps') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($sequence->steps as $step)
                        <div class="rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            <div class="font-medium text-slate-900">{{ $step->order }}. {{ $step->subject }}</div>
                            <div class="mt-1 text-slate-600">{{ __('Delay') }}: {{ $step->delay_days }} {{ __('days') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('No steps yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('leadquality.sequences.update', $sequence) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Update sequence') }}</h2>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Name') }}</span>
                    <input name="name" value="{{ $sequence->name }}" class="w-full rounded-lg border-slate-300" />
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked($sequence->is_active) />
                    {{ __('Active') }}
                </label>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Save') }}</button>
            </form>

            <form method="POST" action="{{ route('leadquality.sequences.steps.store', $sequence) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                @csrf
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Add step') }}</h2>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Delay days') }}</span>
                    <input name="delay_days" type="number" min="0" class="w-full rounded-lg border-slate-300" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Subject') }}</span>
                    <input name="subject" class="w-full rounded-lg border-slate-300" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Body') }}</span>
                    <textarea name="body" rows="5" class="w-full rounded-lg border-slate-300"></textarea>
                </label>
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Add') }}</button>
            </form>
        </div>
    </div>
@endsection
