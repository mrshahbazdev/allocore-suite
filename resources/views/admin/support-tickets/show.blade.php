@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ Str::limit($supportTicket->subject, 60) }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.support_tickets.from') }} {{ $supportTicket->user?->name ?? '—' }} · {{ $supportTicket->team?->name ?? '—' }}</p>
        </div>
        <a href="{{ route('admin.support-tickets.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to tickets') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-3">{{ __('Description') }}</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $supportTicket->body }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('admin.support_tickets.messages') }}</h2>
                <div class="space-y-4">
                    @forelse ($supportTicket->messages as $message)
                        <div class="rounded-lg {{ $message->is_internal ? 'bg-amber-50 border border-amber-100' : 'bg-slate-50' }} p-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-semibold text-slate-900">{{ $message->user?->name ?? __('System') }}</span>
                                <span class="text-xs text-slate-500">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $message->body }}</p>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('admin.support_tickets.no_messages') }}</div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.support-tickets.messages.store', $supportTicket) }}" class="mt-6 space-y-3">
                    @csrf
                    <div>
                        <textarea name="body" rows="3" placeholder="{{ __('admin.support_tickets.message_placeholder') }}" class="block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('admin.support_tickets.internal_note') }}
                        </label>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.support_tickets.add_message') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm h-fit">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('admin.support_tickets.manage') }}</h2>
            <form method="POST" action="{{ route('admin.support-tickets.update', $supportTicket) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                        @foreach (App\Models\SupportTicket::STATUSES as $s)
                            <option value="{{ $s }}" @selected($supportTicket->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.support_tickets.priority') }}</label>
                    <select name="priority" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                        @foreach (App\Models\SupportTicket::PRIORITIES as $p)
                            <option value="{{ $p }}" @selected($supportTicket->priority === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.support_tickets.assigned') }}</label>
                    <select name="assigned_to" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                        <option value="">{{ __('admin.support_tickets.unassigned') }}</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected($supportTicket->assigned_to == $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.support_tickets.category') }}</label>
                    <input name="category" value="{{ old('category', $supportTicket->category) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.support_tickets.update_button') }}</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.support-tickets.destroy', $supportTicket) }}" onsubmit="return confirm('{{ __('admin.support_tickets.confirm_delete') }}')" class="mt-4">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
@endsection
