@extends('layouts.shell')

@section('title', $level->exists ? __('Edit Level') : __('New Level'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $level->exists ? __('Edit Level') : __('New Level') }}</h1>
        <form method="POST" action="{{ $level->exists ? route('bunnyband.admin.levels.update', $level) : route('bunnyband.admin.levels.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($level->exists) @method('PUT') @endif
            <div><input type="text" name="name" value="{{ old('name', $level->name) }}" placeholder="Name" class="w-full rounded-lg border-slate-300" required></div>
            <div><textarea name="description" placeholder="Description" rows="2" class="w-full rounded-lg border-slate-300">{{ old('description', $level->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <select name="type" class="rounded-lg border-slate-300"><option value="free" {{ old('type', $level->type) === 'free' ? 'selected' : '' }}>Free</option><option value="paid" {{ old('type', $level->type) === 'paid' ? 'selected' : '' }}>Paid</option></select>
                <input type="number" step="0.01" name="price" value="{{ old('price', $level->price) }}" placeholder="Price" class="rounded-lg border-slate-300">
                <input type="number" step="0.01" name="daily_earning_limit" value="{{ old('daily_earning_limit', $level->daily_earning_limit) }}" placeholder="Daily earning limit" class="rounded-lg border-slate-300">
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <input type="number" step="0.01" name="referral_bonus" value="{{ old('referral_bonus', $level->referral_bonus) }}" placeholder="Referral bonus" class="rounded-lg border-slate-300">
                <input type="number" step="0.01" name="task_bonus_percent" value="{{ old('task_bonus_percent', $level->task_bonus_percent) }}" placeholder="Task bonus %" class="rounded-lg border-slate-300">
                <input type="number" step="0.01" name="withdrawal_limit" value="{{ old('withdrawal_limit', $level->withdrawal_limit) }}" placeholder="Withdrawal limit" class="rounded-lg border-slate-300">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <input type="number" name="sort_order" value="{{ old('sort_order', $level->sort_order) }}" placeholder="Sort order" class="rounded-lg border-slate-300">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $level->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm">{{ __('Active') }}</span></label>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
