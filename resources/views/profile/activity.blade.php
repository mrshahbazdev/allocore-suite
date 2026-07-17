@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Account Activity') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Recent actions performed on your account.') }}</p>

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3">{{ __('Event') }}</th>
                        <th class="px-4 py-3">{{ __('Description') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $activity->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $activity->log_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">{{ __('No activity recorded yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $activities->links() }}</div>
    </div>
@endsection
