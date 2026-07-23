@extends('install.layout')

@section('content')
    <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Admin Account & Site') }}</h2>

    <form method="POST" action="{{ route('install.run') }}">
        @csrf

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Site name') }}</label>
            <input type="text" name="site_name" value="{{ old('site_name', 'Allocore Suite') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Admin name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Admin email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
            <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div class="mb-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Confirm password') }}</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ __('Install Allocore Suite') }}
        </button>
    </form>
@endsection
