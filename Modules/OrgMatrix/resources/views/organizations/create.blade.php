@extends('layouts.shell', ['title' => __('New Organization')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('New Organization') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('orgmatrix.organizations.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Industry') }}</label>
                <input type="text" name="industry" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>
            <div class="flex justify-end">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
