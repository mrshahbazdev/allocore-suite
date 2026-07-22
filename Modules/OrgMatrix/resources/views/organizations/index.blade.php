@extends('layouts.shell', ['title' => __('Organizations')])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Organizations') }}</h1>
        <a href="{{ route('orgmatrix.organizations.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Organization') }}</a>
    </div>

    @if ($organizations->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No organizations yet.') }}</div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($organizations as $organization)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="font-semibold text-slate-900">{{ $organization->name }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ $organization->industry ?? '' }}</div>
                    <div class="mt-3 flex gap-4 text-sm text-slate-600">
                        <span>{{ $organization->roles_count }} {{ __('roles') }}</span>
                        <span>{{ $organization->people_count }} {{ __('people') }}</span>
                    </div>
                    <div class="mt-4 flex gap-3 text-sm">
                        <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="text-indigo-600 hover:underline">{{ __('Open') }}</a>
                        <a href="{{ route('orgmatrix.organizations.edit', $organization) }}" class="text-slate-600 hover:underline">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('orgmatrix.organizations.destroy', $organization) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
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
