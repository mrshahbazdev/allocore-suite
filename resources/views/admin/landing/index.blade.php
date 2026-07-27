@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Landing Page Builder') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Add, reorder and edit blocks for your home page.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.landing.update') }}">
        @csrf
        @method('PUT')

        @include('admin.partials.blocks-editor', ['name' => 'blocks', 'blocks' => $blocks])

        @if (empty($blocks))
            <p class="mt-4 text-sm text-slate-500">{{ __('No blocks yet. Add one below.') }}</p>
        @endif

        <div class="mt-6 flex items-center justify-end">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save landing page') }}</button>
        </div>
    </form>
@endsection
