@extends('layouts.shell')

@section('title', $department->exists ? __('Edit Department') : __('New Department'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $department->exists ? __('Edit Department') : __('New Department') }}</h1>
        <form method="POST" action="{{ $department->exists ? route('smartkpi.departments.update', $department) : route('smartkpi.companies.departments.store', $company ?? $department->company) }}" class="mt-6 space-y-4">
            @csrf
            @if ($department->exists) @method('PUT') @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $department->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $department->description) }}</textarea></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Parent Department') }}</label><select name="parent_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">{{ __('None') }}</option></select></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Industry Type') }}</label><input type="text" name="industry_type" value="{{ old('industry_type', $department->industry_type) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Size') }}</label><input type="text" name="size" value="{{ old('size', $department->size) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
