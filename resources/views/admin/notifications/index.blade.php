@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.notifications.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.notifications.description') }}</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.notifications.send_button') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.notifications.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Subject') }}</th>
                    <th class="px-4 py-3">{{ __('Recipient') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Read at') }}</th>
                    <th class="px-4 py-3">{{ __('Sent') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $notification->data['subject'] ?? '' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $notification->notifiable?->name ?? $notification->notifiable_id }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $notification->data['type'] ?? 'info' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $notification->read_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $notification->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('{{ __('admin.notifications.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('admin.notifications.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
