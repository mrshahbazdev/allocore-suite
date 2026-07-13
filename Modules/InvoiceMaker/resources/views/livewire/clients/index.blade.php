<div>
    @include('invoicemaker::partials.nav')
    <div class="mb-6 flex items-end justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900">{{ __('Clients') }}</h1><p class="text-sm text-slate-500">{{ __('Customer records shared by invoices and estimates.') }}</p></div>
        <a href="{{ route('invoicemaker.clients.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Add client') }}</a>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4"><input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search clients') }}" class="w-full max-w-sm rounded-lg border-slate-300 text-sm"></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('Contact') }}</th><th class="px-5 py-3">{{ __('Invoices') }}</th><th class="px-5 py-3 text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($clients as $client)
                    <tr>
                        <td class="px-5 py-4"><p class="font-medium text-slate-900">{{ $client->name }}</p><p class="text-xs text-slate-500">{{ $client->company_name }}</p></td>
                        <td class="px-5 py-4 text-slate-600">{{ $client->email ?: '—' }}<br>{{ $client->phone }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $client->invoices_count }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('invoicemaker.clients.edit', $client) }}" class="font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a> <button wire:click="delete({{ $client->id }})" wire:confirm="{{ __('Delete this client?') }}" class="ml-3 text-rose-600 hover:underline">{{ __('Delete') }}</button></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">{{ __('No clients found.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $clients->links() }}</div>
    </div>
</div>
