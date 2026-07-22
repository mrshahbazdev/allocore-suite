@extends('layouts.shell', ['title' => __('Decisions')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Decisions') }}</h1>
        <a href="{{ route('nurdu.decisions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Log Decision') }}</a>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 text-center"><div class="text-2xl font-bold text-emerald-700">{{ $stats['green'] }}</div><div class="text-xs text-emerald-700">{{ __('Green') }}</div></div>
        <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-center"><div class="text-2xl font-bold text-amber-700">{{ $stats['yellow'] }}</div><div class="text-xs text-amber-700">{{ __('Yellow') }}</div></div>
        <div class="rounded-2xl bg-rose-50 border border-rose-100 p-4 text-center"><div class="text-2xl font-bold text-rose-700">{{ $stats['red'] }}</div><div class="text-xs text-rose-700">{{ __('Red') }}</div></div>
    </div>

    @if ($decisions->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No decisions logged yet.') }}</div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 font-medium"><tr><th class="px-4 py-3">{{ __('Title') }}</th><th class="px-4 py-3">{{ __('Alignment') }}</th><th class="px-4 py-3">{{ __('Date') }}</th><th class="px-4 py-3">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($decisions as $decision)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $decision->title }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-medium bg-slate-100">{{ $decision->alignment }}</span></td>
                            <td class="px-4 py-3">{{ $decision->decision_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 flex gap-3">
                                <a href="{{ route('nurdu.decisions.edit', $decision) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('nurdu.decisions.destroy', $decision) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $decisions->links() }}
    @endif
</div>
@endsection
