@extends('layouts.shell')

@section('title', __('Flag Rework'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Flag Rework') }}</h1>

        <form method="POST" action="{{ route('dentaltrack.admin.rework-events.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Order Step') }}</label>
                <select name="dentaltrack_order_step_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">{{ __('Select step') }}</option>
                    @foreach ($steps as $step)
                        <option value="{{ $step->id }}" {{ old('dentaltrack_order_step_id') == $step->id ? 'selected' : '' }}>#{{ $step->order?->id }} - {{ $step->step_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Cause') }}</label>
                <select name="cause" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach (['material_defect','technique_error','equipment_issue','design_error','other'] as $c)
                        <option value="{{ $c }}" {{ old('cause') === $c ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $c)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.rework-events.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Flag') }}</button>
            </div>
        </form>
    </div>
@endsection
