@extends('layouts.shell')

@section('title', __('BunnyBand Settings'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('BunnyBand Settings') }}</h1>
        <form method="POST" action="{{ route('bunnyband.admin.settings.update') }}" class="mt-6 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="text-sm font-medium">{{ __('Welcome Bonus') }}</label><input type="number" step="0.01" name="welcome_bonus" value="{{ $settings['welcome_bonus'] }}" class="w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm font-medium">{{ __('Referral Reward') }}</label><input type="number" step="0.01" name="referral_reward" value="{{ $settings['referral_reward'] }}" class="w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm font-medium">{{ __('Task Reward') }}</label><input type="number" step="0.01" name="task_reward" value="{{ $settings['task_reward'] }}" class="w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm font-medium">{{ __('Minimum Withdrawal') }}</label><input type="number" step="0.01" name="minimum_withdrawal" value="{{ $settings['minimum_withdrawal'] }}" class="w-full rounded-lg border-slate-300"></div>
            </div>
            <div><label class="text-sm font-medium">{{ __('Announcement') }}</label><textarea name="announcement" rows="2" class="w-full rounded-lg border-slate-300">{{ $settings['announcement'] }}</textarea></div>
            <label class="flex items-center gap-2"><input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm">{{ __('Maintenance Mode') }}</span></label>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
