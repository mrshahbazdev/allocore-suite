@extends('layouts.shell')

@section('title', $transaction->exists ? __('Edit Transaction') : __('New Transaction'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $transaction->exists ? __('Edit Transaction') : __('New Transaction') }}</h1>
        <form method="POST" action="{{ $transaction->exists ? route('cashcore.transactions.update', $transaction) : route('cashcore.transactions.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($transaction->exists) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                    <select name="type" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['income' => 'Income', 'expense' => 'Expense'] as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $transaction->type) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Amount') }}</label><input type="number" step="0.01" name="amount" value="{{ old('amount', $transaction->amount) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label><input type="text" name="description" value="{{ old('description', $transaction->description) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Vendor') }}</label><input type="text" name="vendor" value="{{ old('vendor', $transaction->vendor) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                    <select name="cashcore_category_id" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('cashcore_category_id', $transaction->cashcore_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Date') }}</label><input type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Interval') }}</label>
                    <select name="recurring_interval" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="">{{ __('None') }}</option>
                        @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $key => $label)
                            <option value="{{ $key }}" {{ old('recurring_interval', $transaction->recurring_interval) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2"><input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring', $transaction->is_recurring) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Recurring') }}</span></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label><textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('notes', $transaction->notes) }}</textarea></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
