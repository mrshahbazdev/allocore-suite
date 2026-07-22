@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Webhook History') }}: {{ $webhook->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('Recent delivery attempts for this webhook.') }}</p>
        </div>
        <a href="{{ route('admin.integrations.edit', $webhook->integration_id) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to integration') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Event') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Attempts') }}</th>
                    <th class="px-4 py-3">{{ __('Sent at') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($calls as $call)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">{{ $call->event }}</td>
                        <td class="px-4 py-3">
                            @if ($call->isSuccessful())
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ $call->response_status }}</span>
                            @elseif ($call->failed_at)
                                <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700">{{ __('Failed') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $call->attempts }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $call->sent_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($call->failed_at)
                                <form method="POST" action="{{ route('admin.webhook-calls.retry', $call) }}" class="inline">
                                    @csrf
                                    <button class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Retry') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('No webhook calls yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $calls->links() }}</div>
@endsection
