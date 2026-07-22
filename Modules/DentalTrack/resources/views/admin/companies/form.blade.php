@extends('layouts.shell')

@section('title', ($company->id ?? false) ? __('Edit Company') : __('Add Company'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ ($company->id ?? false) ? __('Edit Company') : __('Add Company') }}</h1>

        <form method="POST" action="{{ ($company->id ?? false) ? route('dentaltrack.admin.companies.update', $company) : route('dentaltrack.admin.companies.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @if($company->id ?? false) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $company->name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
                <textarea name="address" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" rows="3">{{ old('address', $company->address) }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-700">{{ __('Active') }}</span>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.companies.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
