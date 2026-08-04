@php($moduleSegment = request()->segment(2))
@php($currentModule = $moduleSegment ? \App\Models\Module::where('route_prefix', $moduleSegment)->where('is_active', true)->first() : null)

@if ($currentModule)
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#ff9200] text-lg font-bold text-white">
                    {{ strtoupper(substr($currentModule->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                        <a href="{{ route('dashboard') }}" class="hover:text-[#ff9200]">{{ __('Dashboard') }}</a>
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        <span class="text-slate-900">{{ $currentModule->name }}</span>
                    </div>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $currentModule->name }}</h1>
                    @if ($currentModule->description)
                        <p class="mt-1 max-w-xl text-sm text-slate-500">{{ Str::limit($currentModule->description, 160) }}</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm hover:border-[#ff9200] hover:text-[#ff9200]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                {{ __('Back to dashboard') }}
            </a>
        </div>
    </div>
@endif
