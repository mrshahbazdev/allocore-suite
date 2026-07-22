@extends('layouts.shell')

@section('title', __('Deposit Methods'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between"><h1 class="text-2xl font-bold text-slate-900">{{ __('Deposit Methods') }}</h1><a href="{{ route('bunnyband.admin.deposit-methods.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('New') }}</a></div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Bank') }}</th><th class="pb-2 pr-4">{{ __('Active') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($methods as $method)
                        <tr>
                            <td class="py-2 pr-4">{{ $method->name }}</td>
                            <td class="py-2 pr-4">{{ $method->bank_name }}</td>
                            <td class="py-2 pr-4">{{ $method->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-2"><a href="{{ route('bunnyband.admin.deposit-methods.edit', $method) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $methods->links() }}</div>
        </div>
    </div>
@endsection
