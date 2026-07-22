@extends('layouts.shell')

@section('title', __('Scan Events'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Scan Events') }}</h1>

        <form method="GET" class="flex gap-3">
            <input type="text" name="order_id" value="{{ request('order_id') }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Order ID') }}">
            <input type="text" name="workstation_id" value="{{ request('workstation_id') }}" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Workstation ID') }}">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Filter') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Time') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Order') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Workstation') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('User') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Event') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Duration') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($events as $event)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $event->scanned_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm"><a href="{{ route('dentaltrack.admin.orders.show', $event->order) }}" class="text-indigo-600 hover:underline">#{{ $event->order?->id }}</a></td>
                            <td class="px-4 py-3 text-sm">{{ $event->workstation?->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $event->user?->name }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_', ' ', $event->event_type->value) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $event->formattedDuration() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-4 text-sm text-slate-500">{{ __('No events found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $events->links() }}</div>
    </div>
@endsection
