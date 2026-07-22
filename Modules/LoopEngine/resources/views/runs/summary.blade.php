@extends('layouts.shell')

@section('title', $run->process->localizedName())
@section('page-title', $run->process->localizedName())

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $run->process->localizedName() }}</h1>
                <p class="text-sm text-slate-500">{{ __('Status') }}: {{ __($run->status) }} — {{ __('Loops') }}: {{ $run->loop_count }} — {{ __('Duration') }}: {{ $run->completed_at ? $run->started_at->diffForHumans($run->completed_at, true) : '-' }}</p>
            </div>
            <a href="{{ route('loopengine.runs.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Back') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Responses') }}</h2>
            <table class="mt-4 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Step') }}</th><th class="pb-2 pr-4">{{ __('Answer') }}</th><th class="pb-2 pr-4">{{ __('Loop') }}</th><th class="pb-2 pr-4">{{ __('Time') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($run->responses as $response)
                        <tr>
                            <td class="py-2 pr-4">{{ $response->step->localizedQuestion() }}</td>
                            <td class="py-2 pr-4">{{ $response->option?->localizedLabel() ?? $response->response_text ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $response->loop_iteration }}</td>
                            <td class="py-2 pr-4">{{ $response->responded_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Audit Log') }}</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @foreach ($run->logs as $log)
                    <li><span class="font-medium">{{ __($log->action) }}</span> — {{ $log->user?->name }} — {{ $log->created_at?->format('Y-m-d H:i') }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
