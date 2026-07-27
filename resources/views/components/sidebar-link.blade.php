@props(['route', 'icon' => null, 'pattern' => null])
@php($isActive = request()->routeIs($pattern ?? $route))
<a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
    @if ($icon)
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
    @else
        <span class="h-2 w-2 shrink-0 rounded-full bg-indigo-400"></span>
    @endif
    <span>{{ $slot }}</span>
</a>
