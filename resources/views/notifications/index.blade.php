@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Notifications') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Your recent notifications') }}</p>
        </div>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            @method('PATCH')
            <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Mark all as read') }}</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm {{ $notification->read_at ? 'opacity-70' : '' }}">
                <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full {{ match($notification->data['type'] ?? 'info') { 'success' => 'bg-emerald-500', 'warning' => 'bg-amber-500', 'danger' => 'bg-rose-500', default => 'bg-indigo-500' } }}"></div>
                <div class="flex-1">
                    <h3 class="font-semibold text-slate-900">{{ $notification->data['subject'] ?? '' }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $notification->data['body'] ?? '' }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('notifications.mark-as-read', $notification) }}">
                                @csrf
                                @method('PATCH')
                                <button class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Mark as read') }}</button>
                            </form>
                        @endif
                        @if (! empty($notification->data['action_url']))
                            <a href="{{ $notification->data['action_url'] }}" class="text-xs font-medium text-indigo-600 hover:underline">{{ $notification->data['action_text'] ?? __('View') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No notifications yet.') }}</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
@endsection
