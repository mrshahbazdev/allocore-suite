@extends('layouts.shell', ['title' => __('Edit Person')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Edit Person') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('orgmatrix.organizations.people.update', [$organization, $person]) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PATCH')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('First Name') }}</label>
                    <input type="text" name="first_name" value="{{ $person->first_name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Last Name') }}</label>
                    <input type="text" name="last_name" value="{{ $person->last_name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ $person->email }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ $person->phone }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Job Title') }}</label>
                    <input type="text" name="title" value="{{ $person->title }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Department') }}</label>
                    <input type="text" name="department" value="{{ $person->department }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $person->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                <label class="text-sm text-slate-700">{{ __('Active') }}</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Avatar') }}</label>
                <input type="file" name="avatar" accept="image/*" class="mt-1 block w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $person->notes }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('orgmatrix.organizations.people.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
