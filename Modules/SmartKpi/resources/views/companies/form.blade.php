@extends('layouts.shell')

@section('title', $company->exists ? __('Edit Company') : __('New Company'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $company->exists ? __('Edit Company') : __('New Company') }}</h1>
        <form method="POST" action="{{ $company->exists ? route('smartkpi.companies.update', $company) : route('smartkpi.companies.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($company->exists) @method('PUT') @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $company->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $company->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Industry') }}</label><input type="text" name="industry" value="{{ old('industry', $company->industry) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Size') }}</label><input type="text" name="size" value="{{ old('size', $company->size) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Timezone') }}</label><input type="text" name="timezone" value="{{ old('timezone', $company->timezone) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
