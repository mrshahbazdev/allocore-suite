@extends('layouts.shell')

@section('title', $blocker->exists ? __('Edit Blocker') : __('New Blocker'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $blocker->exists ? __('Edit Blocker') : __('New Blocker') }}</h1>
        <form method="POST" action="{{ $blocker->exists ? route('cashcore.unlocker.update', $blocker) : route('cashcore.unlocker.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($blocker->exists) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                    <select name="blocker_type" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['open_invoice' => 'Open Invoice', 'payment_terms' => 'Payment Terms', 'inventory' => 'Excess Inventory', 'inefficient_flow' => 'Inefficient Flow'] as $key => $label)
                            <option value="{{ $key }}" {{ old('blocker_type', $blocker->blocker_type) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Amount') }}</label><input type="number" step="0.01" name="blocked_amount" value="{{ old('blocked_amount', $blocker->blocked_amount) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label><input type="text" name="title" value="{{ old('title', $blocker->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $blocker->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Due Date') }}</label><input type="date" name="due_date" value="{{ old('due_date', $blocker->due_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Debtor') }}</label><input type="text" name="debtor_name" value="{{ old('debtor_name', $blocker->debtor_name) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Days Overdue') }}</label><input type="number" name="days_overdue" value="{{ old('days_overdue', $blocker->days_overdue) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                    @foreach (['active' => 'Active', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'] as $key => $label)
                        <option value="{{ $key }}" {{ old('status', $blocker->status) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
