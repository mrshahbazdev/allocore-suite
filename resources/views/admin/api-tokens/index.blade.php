@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.api_tokens.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.api_tokens.description') }}</p>
        </div>
        <a href="{{ route('admin.api-tokens.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.api_tokens.create_button') }}</a>
    </div>

    @if (session('plain_token'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            <p class="font-medium">{{ __('admin.api_tokens.plain_token_message') }}</p>
            <code class="mt-2 block select-all rounded-lg bg-white p-2 font-mono text-slate-700">{{ session('plain_token') }}</code>
        </div>
    @endif

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.api-tokens.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.api_tokens.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('User') }}</th>
                    <th class="px-4 py-3">{{ __('admin.api_tokens.abilities') }}</th>
                    <th class="px-4 py-3">{{ __('admin.api_tokens.last_used') }}</th>
                    <th class="px-4 py-3">{{ __('admin.api_tokens.expires_at') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tokens as $token)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $token->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $token->user->name }} <span class="text-xs text-slate-400">({{ $token->user->email }})</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ implode(', ', $token->abilities ?? ['*']) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $token->last_used_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $token->expires_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.api-tokens.destroy', $token) }}" onsubmit="return confirm('{{ __('admin.api_tokens.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Revoke') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('admin.api_tokens.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tokens->links() }}</div>
@endsection
