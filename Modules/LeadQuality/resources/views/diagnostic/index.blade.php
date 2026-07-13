@extends('layouts.shell')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Lead diagnostic') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Quick questionnaire for a lead quality snapshot.') }}</p>
        </div>

        <form method="POST" action="{{ route('leadquality.diagnostic.store') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                @for ($i = 1; $i <= 10; $i++)
                    <label class="block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Question') }} {{ $i }}</span>
                        <input name="q{{ $i }}" type="number" min="0" max="10" class="w-full rounded-lg border-slate-300" />
                    </label>
                @endfor
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Calculate') }}</button>
        </form>
    </div>
@endsection
