@extends('layouts.shell')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-900">{{ __('cms.new_page') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('cms.pages_description') }}</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.pages._form', ['action' => route('admin.pages.store')])
            </div>
        </div>
    </div>
@endsection
