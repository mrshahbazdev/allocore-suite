@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit incident') }}</h1>
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('admin.status-incidents._form', ['action' => route('admin.status-incidents.update', $statusIncident), 'button' => __('Update'), 'isEdit' => true, 'incident' => $statusIncident])
        </div>
    </div>
@endsection
