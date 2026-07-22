@extends('layouts.shell', ['title' => __('New Vision Check')])

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('New Vision Check') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('nurdu.checks.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Check Date') }}</label>
                <input type="date" name="check_date" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Does what we are doing now clearly pay into our vision?') }}</label>
                <select name="q1_answer" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
                    <option value="yes">{{ __('Yes') }}</option>
                    <option value="partially">{{ __('Partially') }}</option>
                    <option value="no">{{ __('No') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('What decision or activity is currently most moving us away from the vision?') }}</label>
                <textarea name="q2_answer" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('What is the one thing we need to change in the next period to get closer to the vision?') }}</label>
                <textarea name="q3_answer" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Action Items') }}</label>
                <div class="mt-1 space-y-2">
                    <input type="text" name="action_items[]" class="block w-full rounded-lg border-slate-300 shadow-sm">
                    <input type="text" name="action_items[]" class="block w-full rounded-lg border-slate-300 shadow-sm">
                    <input type="text" name="action_items[]" class="block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('nurdu.checks.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
