@php $title = $client->company_name ?? $client->name; @endphp

<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-txmain">{{ $client->company_name ?? $client->name }}</h2>
            <p class="text-txmain">{{ $client->name }}</p>
        </div>
        <a href="{{ route('invoicemaker.clients.index') }}" class="text-brand-600 hover:text-brand-700 text-sm font-medium">
            {{ __('Back to clients') }}
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-card rounded-lg shadow p-6 space-y-3">
            <h3 class="text-lg font-semibold text-txmain">{{ __('Contact') }}</h3>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Email') }}:</span> {{ $client->email ?? '-' }}</div>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Phone') }}:</span> {{ $client->phone ?? '-' }}</div>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Tax number') }}:</span> {{ $client->tax_number ?? '-' }}</div>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Address') }}:</span><br>{!! nl2br(e($client->address ?? '-')) !!}</div>
        </div>

        <div class="bg-card rounded-lg shadow p-6 space-y-3">
            <h3 class="text-lg font-semibold text-txmain">{{ __('Billing') }}</h3>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Currency') }}:</span> {{ $client->currency ?? '-' }}</div>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Language') }}:</span> {{ $client->language ?? '-' }}</div>
            <div class="text-sm text-txmain"><span class="font-medium">{{ __('Notes') }}:</span> {{ $client->notes ?? '-' }}</div>
        </div>
    </div>

    <div class="bg-card rounded-lg shadow">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-txmain">{{ __('Invoices') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-page">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Number') }}</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Date') }}</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-txmain">{{ __('Status') }}</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-txmain">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr class="border-b hover:bg-page">
                            <td class="py-3 px-4 text-txmain">{{ $invoice->invoice_number }}</td>
                            <td class="py-3 px-4 text-txmain">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-txmain">{{ ucfirst($invoice->status) }}</td>
                            <td class="py-3 px-4 text-right text-txmain">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-500">{{ __('No invoices found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
