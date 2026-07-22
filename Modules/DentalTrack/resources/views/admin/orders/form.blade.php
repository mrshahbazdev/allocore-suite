@extends('layouts.shell')

@section('title', ($order->id ?? false) ? __('Edit Order') : __('Add Order'))

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ ($order->id ?? false) ? __('Edit Order') : __('Add Order') }}</h1>

        <form method="POST" action="{{ ($order->id ?? false) ? route('dentaltrack.admin.orders.update', $order) : route('dentaltrack.admin.orders.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @if($order->id ?? false) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label>
                    <select name="dentaltrack_company_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c->id }}" {{ old('dentaltrack_company_id', $order->dentaltrack_company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Lab') }}</label>
                    <select name="dentaltrack_lab_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($labs as $l)
                            <option value="{{ $l->id }}" {{ old('dentaltrack_lab_id', $order->dentaltrack_lab_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Product Type') }}</label>
                <select name="dentaltrack_product_type_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">{{ __('Select') }}</option>
                    @foreach ($productTypes as $pt)
                        <option value="{{ $pt->id }}" {{ old('dentaltrack_product_type_id', $order->dentaltrack_product_type_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Patient Ref') }}</label>
                    <input type="text" name="patient_ref" value="{{ old('patient_ref', $order->patient_ref) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Doctor Name') }}</label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name', $order->doctor_name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Priority') }}</label>
                    <select name="priority" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['low','normal','high','urgent'] as $p)
                            <option value="{{ $p }}" {{ old('priority', $order->priority?->value ?? 'normal') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['pending','in_progress','completed','cancelled','on_hold'] as $s)
                            <option value="{{ $s }}" {{ old('status', $order->status?->value ?? 'pending') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Due Date') }}</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $order->due_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <textarea name="notes" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" rows="3">{{ old('notes', $order->notes) }}</textarea>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.orders.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
