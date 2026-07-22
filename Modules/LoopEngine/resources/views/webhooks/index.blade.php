@extends('layouts.shell')

@section('title', __('Webhooks'))
@section('page-title', __('Webhooks'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Webhooks') }}</h1>
            <a href="{{ route('loopengine.webhooks.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Webhook') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('URL') }}</th><th class="pb-2 pr-4">{{ __('Active') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($webhooks as $webhook)
                        <tr>
                            <td class="py-2 pr-4">{{ $webhook->name }}</td>
                            <td class="py-2 pr-4">{{ Str::limit($webhook->url, 40) }}</td>
                            <td class="py-2 pr-4">{{ $webhook->is_active ? __('Yes') : __('No') }}</td>
                            <td class="py-2 flex gap-2"><a href="{{ route('loopengine.webhooks.edit', $webhook) }}" class="text-indigo-600">{{ __('Edit') }}</a><a href="{{ route('loopengine.webhooks.logs', $webhook) }}" class="text-slate-600">{{ __('Logs') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $webhooks->links() }}</div>
        </div>
    </div>
@endsection
