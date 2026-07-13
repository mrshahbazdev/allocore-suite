@extends('layouts.shell')

@section('content')
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Pipeline') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Quick visual of contacts by stage.') }}</p>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-5">
        @foreach ($pipeline as $stage => $contacts)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $stage) }}</h2>
                <div class="mt-3 space-y-3">
                    @forelse ($contacts as $contact)
                        <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm">
                            <div class="font-medium text-slate-900">{{ $contact->name }}</div>
                            <div class="text-slate-500">{{ $contact->company }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('No contacts') }}</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@endsection
