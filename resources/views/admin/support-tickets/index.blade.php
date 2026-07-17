@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.support_tickets.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.support_tickets.description') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.support-tickets.index') }}" class="flex flex-wrap items-center gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.support_tickets.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm min-w-[200px]">
            <select name="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('admin.support_tickets.all_statuses') }}</option>
                @foreach (App\Models\SupportTicket::STATUSES as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Subject') }}</th>
                    <th class="px-4 py-3">{{ __('User') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('admin.support_tickets.priority') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('admin.support_tickets.assigned') }}</th>
                    <th class="px-4 py-3">{{ __('Created') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tickets as $ticket)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ Str::limit($ticket->subject, 50) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $ticket->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $ticket->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 capitalize">{{ $ticket->priority }}</td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                            {{ $ticket->status === 'open' ? 'bg-emerald-100 text-emerald-800' : ($ticket->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-800') }}">{{ $ticket->status }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $ticket->assignee?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $ticket->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-400">{{ __('admin.support_tickets.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
@endsection
