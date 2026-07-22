@extends('layouts.shell')

@section('title', __('Cash Leaks'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Cash Leaks') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Overall Leak Score') }}: <span class="font-bold">{{ $overallScore }}</span> — {{ __('Total Amount') }}: {{ number_format($totalLeakAmount, 2) }}</p>
            </div>
            <form method="POST" action="{{ route('cashcore.leaks.detect') }}">@csrf<button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Run Detection') }}</button></form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Active Leaks') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Title') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Score') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($activeLeaks as $leak)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $leak->title }}</td>
                            <td class="py-2 pr-4">{{ $leak->leak_type }}</td>
                            <td class="py-2 pr-4">{{ $leak->leak_score }}</td>
                            <td class="py-2 pr-4">{{ number_format($leak->monthly_amount, 2) }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('cashcore.leaks.status', $leak) }}" class="inline">@csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-xs">
                                        @foreach (['detected', 'reviewed', 'resolved', 'ignored'] as $s)
                                            <option value="{{ $s }}" {{ $leak->status === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
