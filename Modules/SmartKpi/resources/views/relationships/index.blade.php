@extends('layouts.shell')

@section('title', __('KPI Relationships'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('KPI Relationships') }}</h1>
            <a href="{{ route('smartkpi.relationships.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Relationship') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Cause') }}</th><th class="pb-2 pr-4">{{ __('Effect') }}</th><th class="pb-2 pr-4">{{ __('Correlation') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($relationships as $relationship)
                        <tr>
                            <td class="py-2 pr-4">{{ $relationship->causeKpi?->localizedName() }}</td>
                            <td class="py-2 pr-4">{{ $relationship->effectKpi?->localizedName() }}</td>
                            <td class="py-2 pr-4">{{ $relationship->correlation }}</td>
                            <td class="py-2"><a href="{{ route('smartkpi.relationships.edit', $relationship) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $relationships->links() }}</div>
        </div>
    </div>
@endsection
