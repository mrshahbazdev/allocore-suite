@extends('layouts.shell', ['title' => __('People')])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $organization->name }} — {{ __('People') }}</h1>
        <a href="{{ route('orgmatrix.organizations.people.create', $organization) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Person') }}</a>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('orgmatrix.organizations.export.people', $organization) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Export CSV') }}</a>
        <span class="text-slate-300">|</span>
        <form method="POST" action="{{ route('orgmatrix.organizations.import.people', $organization) }}" enctype="multipart/form-data" class="flex gap-2 items-center">
            @csrf
            <input type="file" name="csv_file" accept=".csv,.txt" class="text-sm">
            <button class="text-sm text-indigo-600 hover:underline">{{ __('Import CSV') }}</button>
        </form>
    </div>

    @if ($people->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No people yet.') }}</div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($people as $person)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="font-semibold text-slate-900">{{ $person->full_name }}</div>
                    <div class="text-sm text-slate-500">{{ $person->title }} {{ $person->department ? '— '.$person->department : '' }}</div>
                    <div class="text-sm text-slate-600 mt-1">{{ $person->roles->pluck('name')->implode(', ') }}</div>
                    <div class="mt-3 flex gap-2 text-sm">
                        <a href="{{ route('orgmatrix.organizations.people.edit', [$organization, $person]) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('orgmatrix.organizations.people.destroy', [$organization, $person]) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
