@extends('layouts.shell')

@section('title', __('Companies'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Companies') }}</h1>
            <a href="{{ route('smartkpi.companies.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Company') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Industry') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($companies as $company)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $company->name }}</td>
                            <td class="py-2 pr-4">{{ $company->industry }}</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $company->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $company->is_active ? __('Active') : __('Inactive') }}</span></td>
                            <td class="py-2 flex gap-2"><a href="{{ route('smartkpi.companies.show', $company) }}" class="text-indigo-600">{{ __('View') }}</a><a href="{{ route('smartkpi.companies.edit', $company) }}" class="text-slate-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $companies->links() }}</div>
        </div>
    </div>
@endsection
