@php($links = [
    ['route' => 'sweetspot.dashboard', 'active' => 'sweetspot.dashboard', 'label' => __('Dashboard')],
    ['route' => 'sweetspot.customers.index', 'active' => 'sweetspot.customers.*', 'label' => __('Customers')],
    ['route' => 'sweetspot.settings.index', 'active' => 'sweetspot.settings.*', 'label' => __('Settings')],
])

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['active']))
                <a href="{{ route($link['route']) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
