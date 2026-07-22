@extends('layouts.shell')

@section('title', __('My Runs'))
@section('page-title', __('My Runs'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('My Runs') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Process') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2 pr-4">{{ __('Loops') }}</th><th class="pb-2 pr-4">{{ __('Started') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($runs as $run)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $run->process->localizedName() }}</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $run->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($run->status === 'paused' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700') }}">{{ __($run->status) }}</span></td>
                            <td class="py-2 pr-4">{{ $run->loop_count }}</td>
                            <td class="py-2 pr-4">{{ $run->started_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-2"><a href="{{ route('loopengine.runs.show', $run) }}" class="text-indigo-600">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $runs->links() }}</div>
        </div>
    </div>
@endsection
