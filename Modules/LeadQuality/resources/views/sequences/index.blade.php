@extends('layouts.shell')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Sequences') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Automated follow-up campaigns.') }}</p>

            <div class="mt-6 space-y-3">
                @foreach ($sequences as $sequence)
                    <a href="{{ route('leadquality.sequences.show', $sequence) }}" class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-slate-900">{{ $sequence->name }}</div>
                                <div class="text-sm text-slate-500">{{ __('Contacts') }}: {{ $sequence->contacts_count }}</div>
                            </div>
                            <div class="text-sm {{ $sequence->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $sequence->is_active ? __('Active') : __('Paused') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('leadquality.sequences.store') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <h2 class="text-lg font-semibold text-slate-900">{{ __('New sequence') }}</h2>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Name') }}</span>
                <input name="name" class="w-full rounded-lg border-slate-300" />
            </label>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Create') }}</button>
        </form>
    </div>
@endsection
