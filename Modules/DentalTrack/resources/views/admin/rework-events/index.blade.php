@extends('layouts.shell')

@section('title', __('Rework Events'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Rework / Quality Control') }}</h1>
            <a href="{{ route('dentaltrack.admin.rework-events.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Flag Rework') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Order') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Step') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Cause') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Status') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Flagged By') }}</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-500">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($events as $event)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium"><a href="{{ route('dentaltrack.admin.orders.show', $event->order) }}" class="text-indigo-600 hover:underline">#{{ $event->order?->id }}</a></td>
                            <td class="px-4 py-3 text-sm">{{ $event->orderStep?->step_name }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_', ' ', $event->cause->value) }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_', ' ', $event->status->value) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $event->flaggedByUser?->name }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if ($event->status->value !== 'resolved')
                                    <form method="POST" action="{{ route('dentaltrack.admin.rework-events.resolve', $event) }}">@csrf<button class="text-emerald-600 hover:underline">{{ __('Resolve') }}</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-4 text-sm text-slate-500">{{ __('No rework events.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $events->links() }}</div>
    </div>
@endsection
