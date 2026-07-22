@extends('layouts.shell', ['title' => __('Edit Organization')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Edit Organization') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('visionflow.organizations.update', $organization) }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ $organization->name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $organization->description }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Logo URL') }}</label>
                <input type="url" name="logo_url" value="{{ $organization->logo_url }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('visionflow.organizations.show', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
