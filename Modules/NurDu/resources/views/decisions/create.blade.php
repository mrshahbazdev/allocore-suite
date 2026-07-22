@extends('layouts.shell', ['title' => __('Log Decision')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Log Decision') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('nurdu.decisions.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                <input type="text" name="title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Alignment') }}</label>
                <select name="alignment" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    <option value="green">{{ __('Green — Strengthens vision') }}</option>
                    <option value="yellow">{{ __('Yellow — Neutral') }}</option>
                    <option value="red">{{ __('Red — Weakens vision') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Justification') }}</label>
                <textarea name="justification" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Decision Date') }}</label>
                <input type="date" name="decision_date" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('nurdu.decisions.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
