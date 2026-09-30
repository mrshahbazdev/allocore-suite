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
                    <p class="mt-1 text-sm text-slate-500">{{ __('Enter your Allocore Manager credentials. The webhook is configured automatically — no server access needed.') }}</p>

                    <form method="POST" action="{{ route('admin.allocore.tenants') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Manager URL') }}</label>
                            <input name="manager_url" type="url" value="{{ old('manager_url', $managerUrl) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('E-Mail') }}</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                            <input name="password" type="password" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Continue') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
