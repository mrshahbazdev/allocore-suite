@extends('layouts.shell')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Email accounts') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Store inbox connection details for lead detection.') }}</p>

            <div class="mt-6 space-y-3">
                @foreach ($accounts as $account)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-slate-900">{{ $account->email_address }}</div>
                                <div class="text-sm text-slate-500">{{ $account->provider }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('leadquality.email-accounts.test', $account) }}">
                                    @csrf
                                    <button class="text-sm text-indigo-600">{{ __('Test') }}</button>
                                </form>
                                <form method="POST" action="{{ route('leadquality.email-accounts.destroy', $account) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-rose-600">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('leadquality.email-accounts.store') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add account') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach (['email_address','provider','imap_host','imap_port','imap_encryption','smtp_host','smtp_port','smtp_encryption','username','password'] as $field)
                    <label class="block md:col-span-1">
                        <span class="mb-1 block text-sm font-medium text-slate-700">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                        <input name="{{ $field }}" class="w-full rounded-lg border-slate-300" />
                    </label>
                @endforeach
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
