@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('New scheduled report') }}</h1>

        <form method="POST" action="{{ route('scheduled-reports.store') }}" class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @include('scheduled-reports._form', ['report' => null])

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('scheduled-reports.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
