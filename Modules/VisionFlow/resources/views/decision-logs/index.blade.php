@extends('layouts.shell', ['title' => __('Decision Log')])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
@if (session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 mb-6">{{ session('success') }}</div>
@endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Decision Log') }}</h1>
        <a href="{{ route('visionflow.organizations.decision-logs.create', $organization) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New') }}</a>
    </div>
    @if ($items->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No records yet.') }}</div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 font-medium">
                    <tr>
                        <th class="px-4 py-3">{{ __("Title") }}</th><th class="px-4 py-3">{{ __("Value") }}</th><th class="px-4 py-3">{{ __("Mission") }}</th><th class="px-4 py-3">{{ __("By") }}</th>
                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $item->title }}</td><td class="px-4 py-3">{{ $value.title }}</td><td class="px-4 py-3">{{ $mission.title }}</td><td class="px-4 py-3">{{ $user.name }}</td>
                            <td class="px-4 py-3 flex gap-3">
                                
                                <a href="{{ route('visionflow.organizations.decision-logs.edit', [$organization, $item]) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('visionflow.organizations.decision-logs.destroy', [$organization, $item]) }}" onsubmit="return confirm('{{ __('Delete?') }}')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
