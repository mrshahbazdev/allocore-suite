@extends('layouts.shell')

@section('title', ($workstation->id ?? false) ? __('Edit Workstation') : __('Add Workstation'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ ($workstation->id ?? false) ? __('Edit Workstation') : __('Add Workstation') }}</h1>

        <form method="POST" action="{{ ($workstation->id ?? false) ? route('dentaltrack.admin.workstations.update', $workstation) : route('dentaltrack.admin.workstations.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @if($workstation->id ?? false) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Lab') }}</label>
                <select name="dentaltrack_lab_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">{{ __('Select lab') }}</option>
                    @foreach ($labs as $l)
                        <option value="{{ $l->id }}" {{ old('dentaltrack_lab_id', $workstation->dentaltrack_lab_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $workstation->name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                <select name="type" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="station" {{ old('type', $workstation->type?->value) === 'station' ? 'selected' : '' }}>{{ __('Station') }}</option>
                    <option value="waiting_area" {{ old('type', $workstation->type?->value) === 'waiting_area' ? 'selected' : '' }}>{{ __('Waiting Area') }}</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $workstation->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-700">{{ __('Active') }}</span>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.workstations.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
