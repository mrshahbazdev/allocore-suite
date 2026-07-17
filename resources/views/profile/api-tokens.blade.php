@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('API Tokens') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Create and manage personal access tokens for API access.') }}</p>

        @if (session('status'))
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        @if (session('plain_token'))
            <div class="mt-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                <p class="text-sm font-medium text-indigo-900">{{ __('Copy this token now. It will not be shown again.') }}</p>
                <code class="mt-2 block break-all rounded bg-white px-3 py-2 text-sm text-slate-900">{{ session('plain_token') }}</code>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.api-tokens.store') }}" class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Token name') }}</label>
                <input type="text" name="name" required class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm" placeholder="{{ __('e.g. Mobile app') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Abilities') }}</label>
                <input type="text" name="abilities" class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm" placeholder="read, write">
                <p class="mt-1 text-xs text-slate-500">{{ __('Leave empty for full access, or enter comma-separated abilities.') }}</p>
            </div>
            <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Create token') }}</button>
        </form>

        <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Last used') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tokens as $token)
                        <tr>
                            <td class="px-4 py-3 text-slate-900">{{ $token->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $token->last_used_at?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('profile.api-tokens.destroy', $token) }}" onsubmit="return confirm('{{ __('Delete this token?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">{{ __('No API tokens yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $tokens->links() }}</div>
    </div>
@endsection
