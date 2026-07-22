@extends('layouts.shell')

@section('title', $webhook->name)
@section('page-title', $webhook->name)

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Webhook Logs') }}: {{ $webhook->name }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Event') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2 pr-4">{{ __('Code') }}</th><th class="pb-2 pr-4">{{ __('Time') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="py-2 pr-4">{{ $log->event }}</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $log->success ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $log->success ? __('Success') : __('Failed') }}</span></td>
                            <td class="py-2 pr-4">{{ $log->response_code ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
