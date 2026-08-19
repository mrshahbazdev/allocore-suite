@php
$currentTeam = auth()->user()->currentTeam;
$teams = auth()->user()->teams()->with('owner')->get();
@endphp

@if ($currentTeam)
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            <span class="hidden max-w-[10rem] truncate sm:inline">{{ $currentTeam->name }}</span>
            <span class="sm:hidden">{{ strtoupper(substr($currentTeam->name, 0, 1)) }}</span>
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>
        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 z-50 mt-2 w-64 rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
            <div class="px-4 py-2 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Companies') }}</div>
            @foreach ($teams as $team)
                <form method="POST" action="{{ route('teams.switch', $team) }}" class="block">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">
                        <span class="truncate">{{ $team->name }}</span>
                        @if ($currentTeam->id === $team->id)
                            <span class="ml-2 rounded-full bg-[#ff9200]/10 px-2 py-0.5 text-[10px] font-medium text-[#ff9200]">{{ __('Current') }}</span>
                        @endif
                    </button>
                </form>
            @endforeach
            <div class="border-t border-slate-100"></div>
            <a href="{{ route('teams.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('Manage companies') }}</a>
            <a href="{{ route('teams.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#ff9200]">{{ __('+ New company') }}</a>
        </div>
    </div>
@endif
