<div>
    @include('invoicemaker::partials.nav')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6"><h1 class="text-2xl font-bold text-slate-900">{{ $client ? __('Edit client') : __('Add client') }}</h1><p class="text-sm text-slate-500">{{ __('Contact, billing, currency, and language details.') }}</p></div>
        <form wire:submit="save" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ([
                    'name' => [__('Name'), 'text'],
                    'company_name' => [__('Company'), 'text'],
                    'email' => [__('Email'), 'email'],
                    'phone' => [__('Phone'), 'text'],
                    'tax_number' => [__('Tax number'), 'text'],
                    'currency' => [__('Currency'), 'text'],
                    'language' => [__('Language'), 'text'],
                ] as $field => [$label, $type])
                    <label class="block"><span class="text-sm font-medium text-slate-700">{{ $label }}</span><input wire:model="{{ $field }}" type="{{ $type }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error($field)<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                @endforeach
            </div>
            <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Address') }}</span><textarea wire:model="address" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">{{ __('Notes') }}</span><textarea wire:model="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></label>
            <div class="flex justify-end gap-3"><a href="{{ route('invoicemaker.clients.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Cancel') }}</a><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save client') }}</button></div>
        </form>
    </div>
</div>
