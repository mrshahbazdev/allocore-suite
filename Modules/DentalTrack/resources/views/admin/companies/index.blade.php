@extends('layouts.shell')

@section('title', __('Companies'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Companies') }}</h1>
            <a href="{{ route('dentaltrack.admin.companies.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Company') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Name') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Status') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Created') }}</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-500">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($companies as $company)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $company->name }}</td>
                            <td class="px-4 py-3 text-sm"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $company->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $company->is_active ? __('Active') : __('Inactive') }}</span></td>
                            <td class="px-4 py-3 text-sm text-slate-500">{{ $company->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('dentaltrack.admin.companies.edit', $company) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('dentaltrack.admin.companies.destroy', $company) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-4 text-sm text-slate-500">{{ __('No companies found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $companies->links() }}</div>
    </div>
@endsection
