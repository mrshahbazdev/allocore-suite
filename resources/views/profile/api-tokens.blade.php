@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-4xl pb-12">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('API Tokens & MCP Integration') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Create and manage personal access tokens for REST API and Model Context Protocol (MCP) AI assistants.') }}</p>
            </div>
            <a href="{{ route('api-docs.index') }}" class="inline-flex items-center gap-1.5 self-start sm:self-center text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 rounded-xl px-3.5 py-2 transition shadow-xs">
                <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                {{ __('REST API Documentation') }}
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- When a token is created, show the full MCP details and URL banner --}}
        @if (session('plain_token'))
            @include('partials.mcp-token-banner', [
                'token' => session('plain_token'),
                'tokenName' => session('token_name', 'Allocore'),
            ])
        @endif

        <!-- Create Token Form -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">{{ __('Generate New API Token') }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('Enter a descriptive name to identify which app or AI agent will use this token.') }}</p>

            <form method="POST" action="{{ route('profile.api-tokens.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Token name') }}</label>
                    <input type="text" name="name" required class="mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-2.5 text-sm shadow-xs focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('e.g. Cursor MCP, Claude Desktop, Antigravity') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Abilities (optional)') }}</label>
                    <input type="text" name="abilities" class="mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-2.5 text-sm shadow-xs focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('read, write') }}">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Leave empty for full access (*), or enter comma-separated abilities.') }}</p>
                </div>
                <div class="pt-1">
                    <button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Create API Token') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Tokens List -->
        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Active API Tokens') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Tokens currently authorized to access your account data.') }}</p>
                </div>
                <span class="rounded-full bg-slate-200/80 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                    {{ $tokens->total() }} {{ __('Tokens') }}
                </span>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/40 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3.5">{{ __('Name') }}</th>
                        <th class="px-6 py-3.5">{{ __('Abilities') }}</th>
                        <th class="px-6 py-3.5">{{ __('Last used') }}</th>
                        <th class="px-6 py-3.5 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tokens as $token)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $token->name }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">Created {{ $token->created_at?->diffForHumans() ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if (empty($token->abilities) || in_array('*', $token->abilities))
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 border border-emerald-200/60">
                                        {{ __('Full access (*)') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                        {{ implode(', ', $token->abilities) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                {{ $token->last_used_at?->diffForHumans() ?? __('Never') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('profile.api-tokens.destroy', $token) }}" onsubmit="return confirm('{{ __('Revoke and delete this token? Any application using it will lose access immediately.') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('Revoke') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                <svg class="mx-auto h-8 w-8 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <p class="text-sm font-medium text-slate-600">{{ __('No API tokens created yet.') }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ __('Generate a token above to connect external apps and MCP AI clients.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $tokens->links() }}</div>

        <!-- Permanent MCP Server Information Guide Card -->
        <div class="mt-10 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-50 p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-sm">
                    MCP
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('About Allocore Model Context Protocol (MCP)') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Allocore Suite natively implements the standard Model Context Protocol (MCP 2024-11-05).') }}</p>
                </div>
            </div>

            <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                {{ __('MCP allows AI coding agents like Cursor, Claude Desktop, Antigravity, and custom LLM workflows to interact directly with your workspace. Agents can read platform diagnostics, query invoices, run calculations, manage audit tasks, and trigger actions safely using your authorized API tokens.') }}
            </p>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <span class="font-bold text-indigo-700 block mb-1">{{ __('SSE Stream') }}</span>
                    <code class="block font-mono text-[11px] text-slate-800 break-all select-all">{{ url('/api/mcp') }}</code>
                    <p class="text-[11px] text-slate-500 mt-1.5">{{ __('Direct live event stream for Cursor and modern remote MCP connectors.') }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <span class="font-bold text-indigo-700 block mb-1">{{ __('JSON-RPC 2.0') }}</span>
                    <code class="block font-mono text-[11px] text-slate-800 break-all select-all">{{ url('/api/mcp/rpc') }}</code>
                    <p class="text-[11px] text-slate-500 mt-1.5">{{ __('Standard JSON-RPC 2.0 POST endpoint with Bearer authentication.') }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <span class="font-bold text-indigo-700 block mb-1">{{ __('Tool Catalog') }}</span>
                    <code class="block font-mono text-[11px] text-slate-800 break-all select-all">{{ url('/api/mcp/tools') }}</code>
                    <p class="text-[11px] text-slate-500 mt-1.5">{{ __('70+ enterprise tools across all active modules and business diagnostics.') }}</p>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-200/80">
                <span>{{ __('Authentication supported:') }} <code class="font-mono text-slate-700">Bearer &lt;token&gt;</code>, <code class="font-mono text-slate-700">X-Api-Key</code>, {{ __('or') }} <code class="font-mono text-slate-700">?token=&lt;token&gt;</code></span>
                <span class="font-semibold text-indigo-600">{{ __('Zero config needed for clients supporting query tokens') }}</span>
            </div>
        </div>
    </div>
@endsection
