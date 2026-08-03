@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Landing Page Builder') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Add, reorder and edit blocks for your home page.') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('admin.landing.index') }}" class="flex items-center gap-2">
                <label for="landing-locale" class="text-sm font-medium text-slate-700">{{ __('Editing language') }}</label>
                <select id="landing-locale" name="locale" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach (config('app.available_locales', ['en', 'de']) as $l)
                        <option value="{{ $l }}" {{ $locale === $l ? 'selected' : '' }}>
                            {{ config('app.locale_names.'.$l, strtoupper($l)) }}
                            @if ($l === \App\Support\LandingBlocks::BASE_LOCALE)
                                ({{ __('baseline') }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
        </div>
    </div>

    @if ($locale !== \App\Support\LandingBlocks::BASE_LOCALE)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
            {{ __('You are editing the :locale version. Empty fields will fall back to the German baseline.', ['locale' => config('app.locale_names.'.$locale, strtoupper($locale))]) }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.landing.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="locale" value="{{ $locale }}">

        @include('admin.partials.blocks-editor', ['name' => 'blocks', 'blocks' => $blocks])

        @if (empty($blocks))
            <p class="mt-4 text-sm text-slate-500">{{ __('No blocks yet. Add one below.') }}</p>
        @endif

        <div class="mt-6 flex items-center justify-end">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save landing page') }}</button>
        </div>
    </form>
@endsection
