@props(['on'])

<div
    x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => shown = false, 2000); })"
    x-show="shown"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="text-sm font-medium text-emerald-600"
    style="display: none;"
>
    {{ $slot->isEmpty() ? __('Saved.') : $slot }}
</div>
