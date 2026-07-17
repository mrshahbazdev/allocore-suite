@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }} #{{ $item->id }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.module_data.detail_description') }}</p>
        </div>
        <a href="{{ route('admin.module-data.index', [$group, $resource]) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <tbody class="divide-y divide-slate-100">
                @foreach ($item->getAttributes() as $key => $value)
                    @if (! str_starts_with($key, 'created_at') && ! str_starts_with($key, 'updated_at') && $value !== null)
                        <tr>
                            <td class="w-1/3 bg-slate-50 px-4 py-3 font-medium text-slate-700">{{ __(Str::headline(str_replace('_', ' ', $key))) }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if (is_bool($value) || in_array($value, [0, 1, '0', '1'], true))
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $value ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $value ? __('Yes') : __('No') }}</span>
                                @elseif (Str::startsWith($value, '{') || Str::startsWith($value, '['))
                                    <pre class="text-xs">{{ json_encode(json_decode($value), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('admin.module-data.destroy', [$group, $resource, $item->id]) }}" onsubmit="return confirm('{{ __('admin.module_data.confirm_delete') }}')" class="mt-6">
        @csrf
        @method('DELETE')
        <button class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
    </form>
@endsection
