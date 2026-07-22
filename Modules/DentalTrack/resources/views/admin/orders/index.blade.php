@extends('layouts.shell')

@section('title', __('Orders'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Orders') }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('dentaltrack.admin.reports.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Reports') }}</a>
                <a href="{{ route('dentaltrack.admin.orders.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Order') }}</a>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Search patient/ref/doctor') }}">
            <select name="status" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['pending','in_progress','completed','cancelled','on_hold'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('All priorities') }}</option>
                @foreach (['low','normal','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Filter') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Order') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Company / Lab') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Priority') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Status') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Due') }}</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-500">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium"><a href="{{ route('dentaltrack.admin.orders.show', $order) }}" class="text-indigo-600 hover:underline">#{{ $order->id }}</a><div class="text-xs text-slate-500">{{ $order->patient_ref }}</div></td>
                            <td class="px-4 py-3 text-sm">{{ $order->company?->name }}<div class="text-xs text-slate-500">{{ $order->lab?->name }}</div></td>
                            <td class="px-4 py-3 text-sm capitalize">{{ $order->priority->value }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_', ' ', $order->status->value) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $order->due_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('dentaltrack.admin.orders.sticker', $order) }}" class="text-slate-600 hover:underline">{{ __('Sticker') }}</a>
                                <a href="{{ route('dentaltrack.admin.orders.edit', $order) }}" class="text-indigo-600 hover:underline ml-2">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('dentaltrack.admin.orders.destroy', $order) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-4 text-sm text-slate-500">{{ __('No orders found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $orders->links() }}</div>
    </div>
@endsection
