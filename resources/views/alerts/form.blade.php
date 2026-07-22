@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $alert->exists ? __('Edit alert') : __('Create alert') }}</h1>
    </div>

    <form method="POST" action="{{ $alert->exists ? route('alerts.update', $alert) : route('alerts.store') }}" class="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($alert->exists) @method('PATCH') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $alert->name) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Metric') }}</label>
            <select name="metric" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="overdue_invoices" {{ old('metric', $alert->metric) === 'overdue_invoices' ? 'selected' : '' }}>{{ __('Overdue invoices') }}</option>
                <option value="low_cash" {{ old('metric', $alert->metric) === 'low_cash' ? 'selected' : '' }}>{{ __('Cash balance') }}</option>
                <option value="kpi_critical" {{ old('metric', $alert->metric) === 'kpi_critical' ? 'selected' : '' }}>{{ __('Critical KPIs') }}</option>
                <option value="pending_absences" {{ old('metric', $alert->metric) === 'pending_absences' ? 'selected' : '' }}>{{ __('Pending absences') }}</option>
                <option value="custom" {{ old('metric', $alert->metric) === 'custom' ? 'selected' : '' }}>{{ __('Custom') }}</option>
            </select>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Operator') }}</label>
                <select name="operator" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value=">" {{ old('operator', $alert->operator) === '>' ? 'selected' : '' }}>></option>
                    <option value="<" {{ old('operator', $alert->operator) === '<' ? 'selected' : '' }}><</option>
                    <option value=">=" {{ old('operator', $alert->operator) === '>=' ? 'selected' : '' }}>>=</option>
                    <option value="<=" {{ old('operator', $alert->operator) === '<=' ? 'selected' : '' }}><=</option>
                    <option value="=" {{ old('operator', $alert->operator) === '=' ? 'selected' : '' }}>=</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Threshold') }}</label>
                <input type="number" step="0.01" name="threshold" value="{{ old('threshold', $alert->threshold ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Notification method') }}</label>
            <select name="notification_method" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="in_app" {{ old('notification_method', $alert->notification_method) === 'in_app' ? 'selected' : '' }}>{{ __('In-app notification') }}</option>
                <option value="email" {{ old('notification_method', $alert->notification_method) === 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
            </select>
        </div>

        <div class="mb-6 flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $alert->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <label class="text-sm text-slate-700">{{ __('Active') }}</label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $alert->exists ? __('Update') : __('Save') }}</button>
            <a href="{{ route('alerts.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
