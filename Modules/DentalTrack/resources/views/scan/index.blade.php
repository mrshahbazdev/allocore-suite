@extends('layouts.shell')

@section('title', __('QR Scanner'))

@section('content')
    <div class="max-w-xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('QR Scanner') }}</h1>

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($workstation ?? false)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">{{ __('Current workstation') }}</div>
                <div class="text-xl font-semibold text-slate-900">{{ $workstation->name }}</div>
                <div class="text-sm text-slate-500">{{ $workstation->lab?->name }}</div>
            </div>
        @elseif (!($order ?? false))
            <form method="POST" action="{{ route('dentaltrack.scan.process') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Scan workstation QR') }}</label>
                    <input type="text" name="qr_data" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="WS-... or paste URL" autofocus>
                </div>
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Set Workstation') }}</button>
            </form>
        @endif

        @if ($order ?? false)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Order') }}</div>
                    <div class="text-xl font-semibold text-slate-900">#{{ $order->id }} - {{ $order->patient_ref ?? '-' }}</div>
                    <div class="text-sm text-slate-500">{{ $order->productType?->name }}</div>
                </div>

                <form id="scan-form" method="POST" action="{{ route('dentaltrack.scan.process') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="qr_data" value="{{ $order->qr_code }}">
                    <input type="hidden" name="workstation_id" value="{{ session('dentaltrack_workstation_id') }}">

                    <div class="grid grid-cols-2 gap-3">
                        <button type="submit" name="action" value="start" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Start') }}</button>
                        <button type="submit" name="action" value="complete" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Complete') }}</button>
                        <button type="submit" name="action" value="pause" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Pause') }}</button>
                        <button type="submit" name="action" value="transfer" class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-500">{{ __('Transfer') }}</button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                        <textarea name="notes" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" rows="2"></textarea>
                    </div>
                </form>
            </div>
        @elseif (!($workstation ?? false))
            <form method="POST" action="{{ route('dentaltrack.scan.process') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Scan order QR') }}</label>
                    <input type="text" name="qr_data" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ORD-... or paste URL">
                </div>
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Find Order') }}</button>
            </form>
        @endif
    </div>
@endsection
