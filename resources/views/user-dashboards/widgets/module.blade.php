@php($key = $widget['settings']['module_key'] ?? null)
@if ($key && isset($moduleWidgets[$key]))
    @include($moduleWidgets[$key])
@elseif ($key && ! in_array($key, $accessible))
    <p class="text-sm text-slate-500">{{ __('Subscribe to :module to see its widget here.', ['module' => $modules->firstWhere('key', $key)?->name ?? $key]) }}</p>
@else
    <p class="text-sm text-slate-500">{{ __('Module widget not available.') }}</p>
@endif
