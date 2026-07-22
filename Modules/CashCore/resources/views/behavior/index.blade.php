@extends('layouts.shell')

@section('title', __('Behavior'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Behavior & Reviews') }}</h1>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Streak') }}</div><div class="text-2xl font-bold">{{ $streak }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Pending Reviews') }}</div><div class="text-2xl font-bold">{{ $pendingReviews->count() }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Unread Alerts') }}</div><div class="text-2xl font-bold">{{ $alerts->count() }}</div></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Schedule Review') }}</h2>
            <form method="POST" action="{{ route('cashcore.behavior.schedule') }}" class="mt-3 flex items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                    <select name="review_type" class="mt-1 rounded-lg border-slate-300"><option value="monthly">{{ __('Monthly') }}</option><option value="quarterly">{{ __('Quarterly') }}</option><option value="annual">{{ __('Annual') }}</option></select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Date') }}</label><input type="date" name="scheduled_date" class="mt-1 rounded-lg border-slate-300" required></div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Schedule') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Pending Reviews') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($pendingReviews as $review)
                    <li><a href="{{ route('cashcore.behavior.review', $review) }}" class="text-indigo-600">{{ $review->review_type }}</a> — {{ $review->scheduled_date->format('Y-m-d') }}</li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Alerts') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($alerts as $alert)
                    <li class="flex justify-between"><span>{{ $alert->title }}</span>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('cashcore.behavior.alert.read', $alert) }}">@csrf @method('PUT')<button class="text-indigo-600">{{ __('Read') }}</button></form>
                            <form method="POST" action="{{ route('cashcore.behavior.alert.dismiss', $alert) }}">@csrf @method('PUT')<button class="text-slate-500">{{ __('Dismiss') }}</button></form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
