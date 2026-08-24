@props(['route' => null, 'href' => null, 'icon' => null, 'pattern' => null, 'active' => false])
@php($isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN) || ($route && request()->routeIs($pattern ?? $route)))
@php($targetHref = $href ?? ($route ? route($route) : '#'))
<a href="{{ $targetHref }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $isActive ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    @if ($icon)
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
    @else
        <span class="h-2 w-2 shrink-0 rounded-full {{ $isActive ? 'bg-white' : 'bg-slate-500' }}"></span>
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
