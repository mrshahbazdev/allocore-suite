@extends('layouts.public')

@section('title', __('Allocore Suite API'))

@section('content')
<div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('Allocore Suite API') }}</h1>
        <p class="mt-2 text-slate-600">{{ __('Use bearer tokens to access your workspace data and module records programmatically.') }}</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-8">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Authentication') }}</h2>
                <p class="mt-2 text-sm text-slate-600">
                    {{ __('Create a token from your') }} <a href="{{ route('profile.api-tokens.index') }}" class="text-indigo-600 underline">{{ __('API Tokens') }}</a> {{ __('page and send it in the Authorization header.') }}
                </p>
                <div class="mt-4 overflow-x-auto rounded-lg bg-slate-900 p-4 text-sm text-slate-50">
                    <code>GET /api/user</code><br>
                    <code>Authorization: Bearer &lt;your-token&gt;</code>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Endpoints') }}</h2>
                <div class="mt-4 space-y-4">
                    @foreach ([
                        ['GET', '/api/user', __('Current user and team.')],
                        ['GET', '/api/dashboard', __('Dashboard stats, modules and activity.')],
                        ['GET', '/api/modules', __('List all modules with access status and counts.')],
                        ['GET', '/api/modules/{module}', __('Single module stats.')],
                        ['GET', '/api/modules/{module}/records', __('List primary records for a module.')],
                        ['GET', '/api/modules/{module}/records/{id}', __('Show a single module record.')],
                    ] as [$method, $path, $desc])
                        <div class="flex items-start gap-4 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                            <span class="rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">{{ $method }}</span>
                            <div>
                                <code class="text-sm text-slate-800">{{ $path }}</code>
                                <p class="text-sm text-slate-600">{{ $desc }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Query parameters') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('Module record endpoints support pagination and ordering.') }}</p>
                <div class="mt-4 overflow-x-auto rounded-lg bg-slate-900 p-4 text-sm text-slate-50">
                    <code>?per_page=25</code> — {{ __('Items per page (max 100).') }}<br>
                    <code>?with_recent=1</code> — {{ __('Order by most recent first.') }}
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-900">{{ __('Available modules') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($modules as $module)
                        <li class="flex items-center gap-2">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-700">{{ $module->name }}</span>
                            <code class="text-xs text-slate-500">{{ $module->key }}</code>
                        </li>
                    @empty
                        <li class="text-slate-500">{{ __('No API-enabled modules found.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-6">
                <h3 class="font-semibold text-indigo-900">{{ __('Get started') }}</h3>
                <p class="mt-2 text-sm text-indigo-800">
                    {{ __('Generate a token, then query a module you subscribe to.') }}
                </p>
                <div class="mt-4 overflow-x-auto rounded-lg bg-indigo-900/80 p-4 text-sm text-indigo-50">
                    <code>curl {{ url('/api/modules/invoice-maker/records') }} \
                        <br>-H "Authorization: Bearer YOUR_TOKEN"</code>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
