@extends('layouts.shell', ['title' => __('Module permissions')])

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Module permissions') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Choose which tools :name can access in :team.', ['name' => $member->name, 'team' => $team->name]) }}</p>
</div>

<form method="POST" action="{{ route('teams.members.permissions.update', [$team, $member]) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @method('PATCH')

    <div class="space-y-3">
        @foreach ($modules as $module)
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                <input type="checkbox" name="allowed_modules[]" value="{{ $module->key }}" {{ in_array($module->key, $allowed, true) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                <div>
                    <div class="font-medium text-slate-900">{{ $module->name }}</div>
                    <div class="text-sm text-slate-500">{{ $module->description }}</div>
                </div>
            </label>
        @endforeach
    </div>

    <div class="mt-6">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save permissions') }}</button>
    </div>
</form>
@endsection
