@php($links = $links ?? [])
@php($layout = $layout ?? 'horizontal')

@if (count($links))
    @if ($layout === 'vertical')
        <nav class="flex flex-col gap-1" aria-label="{{ __('Module navigation') }}">
            @foreach ($links as $link)
                @php($routeName = $link['route'] ?? '')
                @php($params = $link['params'] ?? [])
                @php($activePattern = $link['active'] ?? (preg_replace('/\.index$/', '', $routeName) . '.*'))
                @php($active = $routeName !== '' && (request()->routeIs($routeName) || request()->routeIs($activePattern)))
                <a href="{{ route($routeName, $params) }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-[#ff9200] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $active ? 'bg-white' : 'bg-slate-500' }} mr-3"></span>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    @else
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <nav class="flex flex-wrap gap-2" aria-label="{{ __('Module navigation') }}">
                @foreach ($links as $link)
                    @php($routeName = $link['route'] ?? '')
                    @php($params = $link['params'] ?? [])
                    @php($activePattern = $link['active'] ?? (preg_replace('/\.index$/', '', $routeName) . '.*'))
                    @php($active = $routeName !== '' && (request()->routeIs($routeName) || request()->routeIs($activePattern)))
                    <a href="{{ route($routeName, $params) }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ $active ? 'bg-[#ff9200] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif
@endif
