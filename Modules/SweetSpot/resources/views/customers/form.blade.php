@extends('layouts.shell', ['title' => $customer->exists ? __('Edit customer') : __('New customer')])

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $customer->exists ? __('Edit customer') : __('New customer') }}</h1>

    <form method="POST" action="{{ $customer->exists ? route('sweetspot.customers.update', $customer) : route('sweetspot.customers.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($customer->exists) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Customer name') }}</label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Industry') }}</label>
                <input type="text" name="industry" value="{{ old('industry', $customer->industry) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Company size') }}</label>
                <input type="text" name="company_size" value="{{ old('company_size', $customer->company_size) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-4 grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Revenue (EUR)') }}</label>
                <input type="number" step="0.01" name="revenue" value="{{ old('revenue', $customer->revenue) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Profit margin (EUR)') }}</label>
                <input type="number" step="0.01" name="profit_margin_eur" value="{{ old('profit_margin_eur', $customer->profit_margin_eur) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Margin %') }}</label>
                <input type="number" step="0.01" name="margin_percent" value="{{ old('margin_percent', $customer->margin_percent) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Effort hours') }}</label>
                <input type="number" step="0.1" name="effort_hours" value="{{ old('effort_hours', $customer->effort_hours) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Repeat rate (%)') }}</label>
                <input type="number" step="0.01" min="0" max="100" name="repeat_rate" value="{{ old('repeat_rate', $customer->repeat_rate) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-4 grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Chemistry score') }} (1-5)</label>
                <select name="chemistry_score" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach (range(1, 5) as $v)
                        <option value="{{ $v }}" {{ old('chemistry_score', $customer->chemistry_score ?? 3) == $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Growth score') }} (1-5)</label>
                <select name="growth_score" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach (range(1, 5) as $v)
                        <option value="{{ $v }}" {{ old('growth_score', $customer->growth_score ?? 3) == $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Payment willingness') }} (1-5)</label>
                <select name="payment_willingness" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach (range(1, 5) as $v)
                        <option value="{{ $v }}" {{ old('payment_willingness', $customer->payment_willingness ?? 3) == $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Recommendations') }}</label>
            <input type="number" name="recommendations" value="{{ old('recommendations', $customer->recommendations) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $customer->notes) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $customer->exists ? __('Update') : __('Save') }}</button>
            <a href="{{ route('sweetspot.customers.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
