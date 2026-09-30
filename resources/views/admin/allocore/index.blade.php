@php($title = __('Allocore Manager'))

@extends('layouts.shell')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-900">{{ __('Allocore Manager') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Connect this suite to Allocore Manager — KPI events (revenue, orders, leads, hours) are pushed automatically.') }}</p>
            </div>

            @if ($webhookUrl)
                <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ __('Connected') }} — {{ $tenantName }}</p>
                            <p class="mt-1 break-all font-mono text-xs text-emerald-800">{{ $webhookUrl }}</p>
                            <p class="mt-1 text-xs text-emerald-700">{{ __('Connected at') }}: {{ $connectedAt }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.allocore.disconnect') }}">
                            @csrf
                            <button class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Disconnect') }}</button>
                        </form>
                    </div>
                </div>
            @elseif (! empty($tenants))
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">{{ __('Choose tenant') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Which Allocore tenant should receive this suite\'s data?') }}</p>

                    <form method="POST" action="{{ route('admin.allocore.link') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Tenant') }}</label>
                            <select name="tenant_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant['id'] }}">{{ $tenant['name'] ?? $tenant['id'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Source name') }}</label>
                            <input name="source_name" value="{{ old('source_name', 'Allocore Suite') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex items-center gap-3">
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Connect') }}</button>
                            <a href="{{ route('admin.allocore.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">{{ __('Connect to Allocore Manager') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('You will be redirected to Allocore Manager to sign in and pick your tenant — then you land back here connected. No credentials stored in this suite.') }}</p>

                    <form method="POST" action="{{ route('admin.allocore.start') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Manager URL') }}</label>
                            <input name="manager_url" type="url" value="{{ old('manager_url', $managerUrl) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5-6h6m0 0v6m0-6L10.5 13.5" /></svg>
                            {{ __('Sign in with Allocore Manager') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
