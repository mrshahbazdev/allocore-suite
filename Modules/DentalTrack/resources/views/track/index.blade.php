@extends('layouts.shell')

@section('title', __('Track Order'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Track Your Order') }}</h1>

        <form method="GET" action="{{ route('dentaltrack.track') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Tracking Code') }}</label>
                <input type="text" name="code" value="{{ $code ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="8-character code" maxlength="8">
            </div>
            <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Track') }}</button>
        </form>

        @isset($order)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Order') }} #{{ $order->id }}</div>
                        <div class="text-xl font-semibold text-slate-900">{{ $order->patient_ref ?? '-' }}</div>
                    </div>
                    <div class="rounded-full px-3 py-1 text-sm font-semibold capitalize {{ $order->status->value === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                        {{ str_replace('_', ' ', $order->status->value) }}
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="text-sm"><span class="text-slate-500">{{ __('Product') }}:</span> {{ $order->productType?->name }}</div>
                    <div class="text-sm"><span class="text-slate-500">{{ __('Lab') }}:</span> {{ $order->lab?->name }}</div>
                    <div class="text-sm"><span class="text-slate-500">{{ __('Doctor') }}:</span> {{ $order->doctor_name ?? '-' }}</div>
                    <div class="text-sm"><span class="text-slate-500">{{ __('Due Date') }}:</span> {{ $order->due_date?->format('Y-m-d') ?? '-' }}</div>
                </div>

                <div>
                    <div class="flex justify-between text-sm font-medium text-slate-700 mb-1">
                        <span>{{ __('Progress') }}</span>
                        <span>{{ $order->progressPercentage() }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $order->progressPercentage() }}%"></div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">{{ __('Steps') }}</h3>
                    <div class="space-y-2">
                        @foreach ($order->steps as $step)
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-2">
                                <span class="text-sm">{{ $step->sort_order }}. {{ $step->step_name }}</span>
                                <span class="text-xs font-semibold capitalize {{ $step->status->value === 'done' ? 'text-emerald-600' : ($step->status->value === 'in_progress' ? 'text-indigo-600' : 'text-slate-500') }}">{{ str_replace('_', ' ', $step->status->value) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif(request('code'))
            <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">{{ __('No order found with this tracking code.') }}</div>
        @endif
    </div>
@endsection
