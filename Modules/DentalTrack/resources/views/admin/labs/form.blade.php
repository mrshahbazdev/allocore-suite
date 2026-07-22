@extends('layouts.shell')

@section('title', ($lab->id ?? false) ? __('Edit Lab') : __('Add Lab'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ ($lab->id ?? false) ? __('Edit Lab') : __('Add Lab') }}</h1>

        <form method="POST" action="{{ ($lab->id ?? false) ? route('dentaltrack.admin.labs.update', $lab) : route('dentaltrack.admin.labs.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @if($lab->id ?? false) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label>
                <select name="dentaltrack_company_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">{{ __('Select company') }}</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" {{ old('dentaltrack_company_id', $lab->dentaltrack_company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $lab->name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Location') }}</label>
                <input type="text" name="location" value="{{ old('location', $lab->location) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $lab->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-700">{{ __('Active') }}</span>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.labs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
