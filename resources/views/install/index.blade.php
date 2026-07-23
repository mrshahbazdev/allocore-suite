@extends('install.layout')

@section('content')
    <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('System Requirements') }}</h2>

    <ul class="mb-6 space-y-2">
        @foreach ($checks as $label => $ok)
            <li class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-2 text-sm {{ $ok ? 'bg-emerald-50' : 'bg-rose-50' }}">
                <span>{{ $label }}</span>
                <span class="font-semibold {{ $ok ? 'text-emerald-700' : 'text-rose-700' }}">{{ $ok ? 'OK' : 'FAIL' }}</span>
            </li>
        @endforeach
    </ul>

    <form method="GET" action="{{ route('install.database') }}">
        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50" {{ $passed ? '' : 'disabled' }}>
            {{ $passed ? __('Next: Database') : __('Fix the failed checks to continue') }}
        </button>
    </form>
@endsection
