@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.module_data.description') }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($resources as $res)
            <a href="{{ route('admin.module-data.index', [$group, $res]) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $resource === $res ? 'bg-indigo-600 text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">{{ __(Str::headline(str_replace('-', ' ', $res))) }}</a>
        @endforeach
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.module-data.index', [$group, $resource]) }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        @foreach ($columns as $column)
                            <th class="px-4 py-3">{{ __(Str::headline(str_replace('_', ' ', $column))) }}</th>
                        @endforeach
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $item->id }}</td>
                            @foreach ($columns as $column)
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    @php($value = $item->{$column})
                                    @if (is_bool($value))
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $value ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $value ? __('Yes') : __('No') }}</span>
                                    @elseif ($column === 'created_at' && $value)
                                        {{ $value->format('d.m.Y H:i') }}
                                    @elseif (is_array($value))
                                        {{ json_encode($value, JSON_UNESCAPED_SLASHES) }}
                                    @else
                                        {{ Str::limit($value, 50) }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.module-data.show', [$group, $resource, $item->id]) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) + 2 }}" class="px-4 py-6 text-center text-slate-400">{{ __('No records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
