@extends('layouts.shell', ['title' => __('People')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ $organization->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('People & role assignments') }}</p>
        </div>
        <a href="{{ route('orgmatrix.organizations.people.create', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('New Person') }}
        </a>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <a href="{{ route('orgmatrix.organizations.export.people', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            {{ __('Export CSV') }}
        </a>
        <form method="POST" action="{{ route('orgmatrix.organizations.import.people', $organization) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="csv_file" accept=".csv,.txt" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 file:mr-2 file:border-0 file:bg-transparent file:text-sm file:font-medium">
            <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                {{ __('Import CSV') }}
            </button>
        </form>
    </div>

    @if ($people->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('No people yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Add people to start assigning roles.') }}</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($people as $person)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            @if ($person->avatar)
                                <img src="{{ Storage::url($person->avatar) }}" alt="" class="h-12 w-12 rounded-full object-cover">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-sm font-bold">
                                    {{ substr($person->first_name, 0, 1) }}{{ substr($person->last_name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-slate-900">{{ $person->full_name }}</div>
                                <div class="text-sm text-slate-500">{{ $person->title }}{{ $person->department ? ' — '.$person->department : '' }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($person->roles->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($person->roles as $role)
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('orgmatrix.organizations.people.edit', [$organization, $person]) }}" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                            {{ __('Edit') }}
                        </a>
                        <form method="POST" action="{{ route('orgmatrix.organizations.people.destroy', [$organization, $person]) }}" onsubmit="return confirm('{{ __('Delete this person?') }}')">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-3 py-1.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
