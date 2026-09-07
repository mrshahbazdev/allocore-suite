@php($status = $status ?? 'unassigned')
@php($styles = [
    'planned' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'reading' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
    'read' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    'unassigned' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
])
@php($labels = [
    'planned' => __('Planned Reading'),
    'reading' => __('Currently Reading'),
    'read' => __('Read'),
    'unassigned' => __('Not in reading plan'),
])

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $styles[$status] ?? $styles['unassigned'] }}">
    {{ $labels[$status] ?? ucfirst($status) }}
</span>
