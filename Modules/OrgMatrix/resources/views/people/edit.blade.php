@extends('layouts.shell', ['title' => __('Edit Person')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('orgmatrix.organizations.people.index', $organization) }}" class="hover:text-indigo-600">{{ __('People') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ $person->full_name }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Person') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('orgmatrix.organizations.people.update', [$organization, $person]) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PATCH')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('First Name') }}</label>
                    <input type="text" name="first_name" value="{{ $person->first_name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Last Name') }}</label>
                    <input type="text" name="last_name" value="{{ $person->last_name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ $person->email }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Phone') }}</label>
                    <input type="text" name="phone" value="{{ $person->phone }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Job Title') }}</label>
                    <input type="text" name="title" value="{{ $person->title }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Department') }}</label>
                    <input type="text" name="department" value="{{ $person->department }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $person->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Avatar') }}</label>
                @if ($person->avatar)
                    <img src="{{ Storage::url($person->avatar) }}" alt="" class="mb-2 h-16 w-16 rounded-full object-cover">
                @endif
                <input type="file" name="avatar" accept="image/*" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:border-0 file:bg-transparent file:font-medium">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <textarea name="notes" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $person->notes }}</textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('orgmatrix.organizations.people.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update Person') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
