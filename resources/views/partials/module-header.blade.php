@php($moduleSegment = request()->segment(2))
@php($currentModule = $moduleSegment ? \App\Models\Module::where('route_prefix', $moduleSegment)->where('is_active', true)->first() : null)

@if ($currentModule)
    <div class="border-b border-slate-200 bg-white">
        <div class="flex items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="text-sm text-slate-500 hover:text-[#ff9200]">
                {{ __('Dashboard') }}
            </a>
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded bg-gradient-to-br from-[#ff9200] to-[#0094af] text-[10px] font-bold text-white">
                    {{ strtoupper(substr($currentModule->name, 0, 1)) }}
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ $currentModule->name }}</span>
            </div>
        </div>
    </div>
@endif
