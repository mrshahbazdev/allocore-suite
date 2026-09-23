@php
    $token = $token ?? session('plain_token');
    $tokenName = $tokenName ?? session('token_name', 'Allocore');
    $baseUrl = url('/');
    $mcpSseUrl = url('/api/mcp?token=' . $token);
    $mcpRpcUrl = url('/api/mcp/rpc');
    $mcpToolsUrl = url('/api/mcp/tools?token=' . $token);
    $mcpResourcesUrl = url('/api/mcp/resources?token=' . $token);
    $mcpPromptsUrl = url('/api/mcp/prompts?token=' . $token);
@endphp

<div
    x-data="{
        activeTab: 'cursor',
        copied: {},
        copy(text, key) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copied[key] = true;
                    setTimeout(() => this.copied[key] = false, 2500);
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-999999px';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    this.copied[key] = true;
                    setTimeout(() => this.copied[key] = false, 2500);
                } catch (e) {
                    console.error('Copy failed', e);
                }
                document.body.removeChild(ta);
            }
        }
    }"
    class="mb-8 rounded-2xl border-2 border-indigo-500/30 bg-gradient-to-b from-indigo-50/70 via-white to-white p-5 sm:p-7 shadow-lg shadow-indigo-500/5 transition-all"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-indigo-100 pb-5">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">{{ __('API Token & MCP Connection Ready') }}</h2>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ __('Active') }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                    {{ __('Use this token with the REST API or connect directly to AI assistants via Model Context Protocol (MCP).') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-center">
            <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-100/80 px-3 py-1.5 text-xs font-semibold text-indigo-800">
                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                MCP Protocol 2024-11-05
            </span>
        </div>
    </div>

    <!-- Security Warning & Token Display -->
    <div class="mt-5 space-y-2">
        <div class="flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('Your Generated API Token') }}</label>
            <span class="text-xs font-semibold text-rose-600 flex items-center gap-1">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ __('Copy now! This token will never be shown again.') }}
            </span>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <div class="relative flex-1">
                <code class="block w-full select-all break-all rounded-xl border border-slate-300 bg-slate-900 px-4 py-3 font-mono text-xs sm:text-sm text-indigo-300 shadow-inner">
                    {{ $token }}
                </code>
            </div>
            <button
                type="button"
                @click="copy('{{ $token }}', 'main_token')"
                class="flex shrink-0 items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition-all shadow-sm"
                :class="copied['main_token'] ? 'bg-emerald-600 text-white shadow-emerald-200' : 'bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 shadow-indigo-200'"
            >
                <svg x-show="!copied['main_token']" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                <svg x-show="copied['main_token']" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span x-text="copied['main_token'] ? '{{ __('Token Copied!') }}' : '{{ __('Copy Token') }}'"></span>
            </button>
        </div>
    </div>

    <!-- MCP Integration Section -->
    <div class="mt-7 rounded-2xl border border-indigo-200/80 bg-white p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 font-bold text-xs">
                    MCP
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('Connect to AI Clients (Cursor, Claude, Antigravity)') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Select your client below to get pre-configured connection parameters.') }}</p>
                </div>
            </div>

            <!-- Client Tabs -->
            <div class="flex flex-wrap items-center gap-1 rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                <button
                    type="button"
                    @click="activeTab = 'cursor'"
                    :class="activeTab === 'cursor' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="rounded-lg px-3 py-1.5 transition"
                >
                    Cursor IDE
                </button>
                <button
                    type="button"
                    @click="activeTab = 'claude'"
                    :class="activeTab === 'claude' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="rounded-lg px-3 py-1.5 transition"
                >
                    Claude Desktop
                </button>
                <button
                    type="button"
                    @click="activeTab = 'endpoints'"
                    :class="activeTab === 'endpoints' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="rounded-lg px-3 py-1.5 transition"
                >
                    {{ __('Direct URLs') }}
                </button>
                <button
                    type="button"
                    @click="activeTab = 'curl'"
                    :class="activeTab === 'curl' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="rounded-lg px-3 py-1.5 transition"
                >
                    cURL / CLI
                </button>
            </div>
        </div>

        <!-- TAB 1: Cursor IDE (SSE) -->
        <div x-show="activeTab === 'cursor'" class="mt-5 space-y-4" x-cloak>
            <div class="rounded-xl bg-indigo-50/70 border border-indigo-100 p-4 text-xs text-indigo-950">
                <p class="font-bold text-indigo-900 mb-1 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('How to connect in Cursor Settings:') }}
                </p>
                <ol class="list-decimal list-inside space-y-1 text-slate-700 ml-1">
                    <li>{{ __('Open Cursor') }} &rarr; <strong>{{ __('Settings') }}</strong> &rarr; <strong>{{ __('Features') }}</strong> &rarr; <strong>{{ __('MCP') }}</strong></li>
                    <li>{{ __('Click') }} <strong>{{ __('"Add New MCP Server"') }}</strong></li>
                    <li>{{ __('Set Server Name to') }} <code class="rounded bg-indigo-100 px-1 py-0.5 font-mono text-indigo-800">Allocore</code></li>
                    <li>{{ __('Set Server Type to') }} <code class="rounded bg-indigo-100 px-1 py-0.5 font-mono text-indigo-800">sse</code></li>
                    <li>{{ __('Paste the Remote MCP Server URL below:') }}</li>
                </ol>
            </div>

            <!-- Server URL Box -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold text-slate-300 uppercase tracking-wider text-[11px]">{{ __('Cursor MCP SSE URL') }}</span>
                    <button
                        type="button"
                        @click="copy('{{ $mcpSseUrl }}', 'cursor_url')"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition"
                    >
                        <span x-text="copied['cursor_url'] ? '{{ __('Copied!') }}' : '{{ __('Copy URL') }}'"></span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <code class="block flex-1 break-all font-mono text-xs text-emerald-400 select-all">{{ $mcpSseUrl }}</code>
                </div>
            </div>

            <!-- Cursor JSON Configuration -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold text-slate-300 uppercase tracking-wider text-[11px]">{{ __('Or add to .cursor/mcp.json') }}</span>
                    <button
                        type="button"
                        @click="copy($refs.cursorJson.innerText.trim(), 'cursor_json')"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition"
                    >
                        <span x-text="copied['cursor_json'] ? '{{ __('Copied!') }}' : '{{ __('Copy JSON') }}'"></span>
                    </button>
                </div>
                <pre class="overflow-x-auto text-xs font-mono text-indigo-200" x-ref="cursorJson"><code>{
  "mcpServers": {
    "allocore": {
      "url": "{{ $mcpSseUrl }}"
    }
  }
}</code></pre>
            </div>
        </div>

        <!-- TAB 2: Claude Desktop -->
        <div x-show="activeTab === 'claude'" class="mt-5 space-y-4" x-cloak>
            <p class="text-xs text-slate-600">
                {{ __('Configure Claude Desktop by adding Allocore to your') }} <code class="font-mono text-xs bg-slate-100 text-slate-800 px-1 py-0.5 rounded">claude_desktop_config.json</code>:
            </p>

            <!-- Option 1: Direct SSE / Remote URL (Modern Claude) -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold text-slate-300 uppercase tracking-wider text-[11px]">{{ __('Option A: Direct SSE URL (Fastest, No Python)') }}</span>
                    <button
                        type="button"
                        @click="copy($refs.claudeSseJson.innerText.trim(), 'claude_sse_json')"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition"
                    >
                        <span x-text="copied['claude_sse_json'] ? '{{ __('Copied!') }}' : '{{ __('Copy JSON') }}'"></span>
                    </button>
                </div>
                <pre class="overflow-x-auto text-xs font-mono text-emerald-400" x-ref="claudeSseJson"><code>{
  "mcpServers": {
    "allocore": {
      "url": "{{ $mcpSseUrl }}"
    }
  }
}</code></pre>
            </div>

            <!-- Option 2: Python Stdio Bridge -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold text-slate-300 uppercase tracking-wider text-[11px]">{{ __('Option B: Stdio HTTPS Bridge (via bridge.py)') }}</span>
                    <button
                        type="button"
                        @click="copy($refs.claudeBridgeJson.innerText.trim(), 'claude_bridge_json')"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition"
                    >
                        <span x-text="copied['claude_bridge_json'] ? '{{ __('Copied!') }}' : '{{ __('Copy Config') }}'"></span>
                    </button>
                </div>
                <pre class="overflow-x-auto text-xs font-mono text-indigo-200" x-ref="claudeBridgeJson"><code>{
  "mcpServers": {
    "allocore": {
      "command": "python",
      "args": [
        "allocore-mcp/bridge.py"
      ],
      "env": {
        "ALLOCORE_BASE_URL": "{{ $baseUrl }}",
        "ALLOCORE_API_TOKEN": "{{ $token }}"
      }
    }
  }
}</code></pre>
            </div>

            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600">
                <span class="font-semibold text-slate-800">{{ __('Config File Location:') }}</span>
                <ul class="list-disc list-inside mt-1 space-y-0.5 font-mono text-[11px] text-slate-500">
                    <li>macOS: <code>~/Library/Application Support/Claude/claude_desktop_config.json</code></li>
                    <li>Windows: <code>%APPDATA%\Claude\claude_desktop_config.json</code></li>
                </ul>
            </div>
        </div>

        <!-- TAB 3: Direct Endpoints & Discovery -->
        <div x-show="activeTab === 'endpoints'" class="mt-5 space-y-2.5" x-cloak>
            <p class="text-xs text-slate-500 mb-2">
                {{ __('Allocore Suite exposes standard Model Context Protocol endpoints compatible with any agent, proxy, or gateway:') }}
            </p>

            <!-- SSE -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-indigo-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">SSE</span>
                        <span class="text-xs font-bold text-slate-800">{{ __('Live Server-Sent Events Endpoint') }}</span>
                    </div>
                    <code class="block font-mono text-xs text-slate-700 break-all select-all">{{ $mcpSseUrl }}</code>
                </div>
                <button
                    type="button"
                    @click="copy('{{ $mcpSseUrl }}', 'ep_sse')"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-indigo-600 self-start sm:self-center shrink-0 transition"
                >
                    <span x-text="copied['ep_sse'] ? '{{ __('Copied!') }}' : '{{ __('Copy URL') }}'"></span>
                </button>
            </div>

            <!-- JSON-RPC -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-slate-700 px-2 py-0.5 text-[10px] font-bold text-white uppercase">POST</span>
                        <span class="text-xs font-bold text-slate-800">{{ __('JSON-RPC 2.0 Protocol Handler') }}</span>
                    </div>
                    <code class="block font-mono text-xs text-slate-700 break-all select-all">{{ $mcpRpcUrl }}</code>
                    <p class="text-[11px] text-slate-500">{{ __('Header:') }} <code class="font-mono text-slate-700">Authorization: Bearer {{ $token }}</code></p>
                </div>
                <button
                    type="button"
                    @click="copy('{{ $mcpRpcUrl }}', 'ep_rpc')"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-indigo-600 self-start sm:self-center shrink-0 transition"
                >
                    <span x-text="copied['ep_rpc'] ? '{{ __('Copied!') }}' : '{{ __('Copy URL') }}'"></span>
                </button>
            </div>

            <!-- Tools Catalog -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">GET</span>
                        <span class="text-xs font-bold text-slate-800">{{ __('Tools Catalog Discovery (70+ Tools)') }}</span>
                    </div>
                    <code class="block font-mono text-xs text-slate-700 break-all select-all">{{ $mcpToolsUrl }}</code>
                </div>
                <button
                    type="button"
                    @click="copy('{{ $mcpToolsUrl }}', 'ep_tools')"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-indigo-600 self-start sm:self-center shrink-0 transition"
                >
                    <span x-text="copied['ep_tools'] ? '{{ __('Copied!') }}' : '{{ __('Copy URL') }}'"></span>
                </button>
            </div>

            <!-- Resources Discovery -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-sky-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">GET</span>
                        <span class="text-xs font-bold text-slate-800">{{ __('Resources Discovery (24 Resources)') }}</span>
                    </div>
                    <code class="block font-mono text-xs text-slate-700 break-all select-all">{{ $mcpResourcesUrl }}</code>
                </div>
                <button
                    type="button"
                    @click="copy('{{ $mcpResourcesUrl }}', 'ep_res')"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-indigo-600 self-start sm:self-center shrink-0 transition"
                >
                    <span x-text="copied['ep_res'] ? '{{ __('Copied!') }}' : '{{ __('Copy URL') }}'"></span>
                </button>
            </div>

            <!-- Prompts Discovery -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-purple-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">GET</span>
                        <span class="text-xs font-bold text-slate-800">{{ __('Prompts Discovery (20 AI Prompts)') }}</span>
                    </div>
                    <code class="block font-mono text-xs text-slate-700 break-all select-all">{{ $mcpPromptsUrl }}</code>
                </div>
                <button
                    type="button"
                    @click="copy('{{ $mcpPromptsUrl }}', 'ep_prompts')"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-indigo-600 self-start sm:self-center shrink-0 transition"
                >
                    <span x-text="copied['ep_prompts'] ? '{{ __('Copied!') }}' : '{{ __('Copy URL') }}'"></span>
                </button>
            </div>
        </div>

        <!-- TAB 4: cURL / CLI Verification -->
        <div x-show="activeTab === 'curl'" class="mt-5 space-y-4" x-cloak>
            <p class="text-xs text-slate-600">
                {{ __('Run these quick terminal tests to instantly verify that your token is authorized and explore live MCP capabilities:') }}
            </p>

            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold text-slate-300 uppercase tracking-wider text-[11px]">{{ __('1. Inspect Available Tools (GET /api/mcp/tools)') }}</span>
                    <button
                        type="button"
                        @click="copy($refs.curlTools.innerText.trim(), 'curl_tools')"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition"
                    >
                        <span x-text="copied['curl_tools'] ? '{{ __('Copied!') }}' : '{{ __('Copy Command') }}'"></span>
                    </button>
                </div>
                <pre class="overflow-x-auto text-xs font-mono text-emerald-400" x-ref="curlTools"><code>curl -s -X GET "{{ $mcpToolsUrl }}"</code></pre>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold text-slate-300 uppercase tracking-wider text-[11px]">{{ __('2. Execute JSON-RPC 2.0 Ping (POST /api/mcp/rpc)') }}</span>
                    <button
                        type="button"
                        @click="copy($refs.curlRpc.innerText.trim(), 'curl_rpc')"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition"
                    >
                        <span x-text="copied['curl_rpc'] ? '{{ __('Copied!') }}' : '{{ __('Copy Command') }}'"></span>
                    </button>
                </div>
                <pre class="overflow-x-auto text-xs font-mono text-indigo-200" x-ref="curlRpc"><code>curl -X POST "{{ $mcpRpcUrl }}" \
  -H "Authorization: Bearer {{ $token }}" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'</code></pre>
            </div>
        </div>

        <!-- Capability Counters Footer -->
        <div class="mt-6 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-center">
            <div class="rounded-xl bg-slate-50/80 p-3 border border-slate-100">
                <span class="block text-xl font-black text-indigo-600">70+</span>
                <span class="text-xs font-semibold text-slate-700">{{ __('Autonomous Tools') }}</span>
                <p class="text-[10px] text-slate-400 mt-0.5">AuditPro, Snowball, Invoices, CRM</p>
            </div>
            <div class="rounded-xl bg-slate-50/80 p-3 border border-slate-100">
                <span class="block text-xl font-black text-indigo-600">24</span>
                <span class="text-xs font-semibold text-slate-700">{{ __('Dynamic Resources') }}</span>
                <p class="text-[10px] text-slate-400 mt-0.5">allocore:// URIs</p>
            </div>
            <div class="rounded-xl bg-slate-50/80 p-3 border border-slate-100">
                <span class="block text-xl font-black text-indigo-600">20</span>
                <span class="text-xs font-semibold text-slate-700">{{ __('AI Prompt Templates') }}</span>
                <p class="text-[10px] text-slate-400 mt-0.5">Audits, OKRs, Cashflow, SOPs</p>
            </div>
        </div>
    </div>
</div>
