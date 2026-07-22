@extends('layouts.shell')

@section('title', __('Cash Unlocker'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Cash Unlocker') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Total blocked') }}: <span class="font-bold">{{ number_format($totalBlocked, 2) }}</span></p>
            </div>
            <a href="{{ route('cashcore.unlocker.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Blocker') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Active Blockers') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Title') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($activeBlockers as $blocker)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $blocker->title }}</td>
                            <td class="py-2 pr-4">{{ $blocker->blocker_type }}</td>
                            <td class="py-2 pr-4">{{ number_format($blocker->blocked_amount, 2) }}</td>
                            <td class="py-2 pr-4">{{ $blocker->status }}</td>
                            <td class="py-2"><a href="{{ route('cashcore.unlocker.edit', $blocker) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
