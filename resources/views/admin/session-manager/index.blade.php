@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.session_manager.title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('admin.session_manager.description') }}</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('User') }}</th>
                    <th class="px-4 py-3">{{ __('IP Address') }}</th>
                    <th class="px-4 py-3">{{ __('User Agent') }}</th>
                    <th class="px-4 py-3">{{ __('Last Activity') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sessions as $session)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $users[$session->user_id] ?? $session->user_id }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $session->ip_address }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ Str::limit($session->user_agent, 60) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ now()->setTimestamp($session->last_activity)->format('d.m.Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.session-manager.destroy', $session->id) }}" onsubmit="return confirm('{{ __('admin.session_manager.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Invalidate') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('admin.session_manager.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $sessions->links() }}</div>
@endsection
