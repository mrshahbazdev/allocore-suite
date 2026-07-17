@extends('layouts.shell')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-900">{{ __('mail.admin_title') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('mail.admin_description') }}</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.mail-settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.driver') }}</label>
                            <select name="driver" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="smtp" {{ old('driver', $setting?->driver) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.host') }}</label>
                            <input name="host" type="text" value="{{ old('host', $setting?->host) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.port') }}</label>
                            <input name="port" type="number" min="1" max="65535" value="{{ old('port', $setting?->port ?? 587) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.encryption') }}</label>
                            <select name="encryption" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="none" {{ old('encryption', $setting?->encryption) === null ? 'selected' : '' }}>{{ __('mail.encryption_none') }}</option>
                                <option value="tls" {{ old('encryption', $setting?->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('encryption', $setting?->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.username') }}</label>
                            <input name="username" type="text" value="{{ old('username', $setting?->username) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.password') }}</label>
                            <input name="password" type="password" value="" placeholder="{{ $setting?->password ? __('mail.password_unchanged') : '' }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @if ($setting?->password)
                                <p class="mt-1 text-xs text-slate-500">{{ __('mail.password_unchanged') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.from_address') }}</label>
                            <input name="from_address" type="email" value="{{ old('from_address', $setting?->from_address) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('mail.from_name') }}</label>
                            <input name="from_name" type="text" value="{{ old('from_name', $setting?->from_name) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                            {{ __('mail.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
