@php $title = __('Templates'); @endphp

<div>
 <div class="mb-8">
 <h2 class="text-2xl font-bold text-txmain">{{ __('Invoice Templates') }}</h2>
 <p class="text-txmain">{{ __('Customize your invoice templates') }}</p>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @forelse($templates as $template)
 <div
 class="bg-card rounded-lg shadow overflow-hidden {{ $template->is_default ? 'ring-2 ring-brand-500' : '' }}">
 <div class="h-32 flex items-center justify-center" style="background: {{ $template->primary_color }}20;">
 <div class="text-center">
 <div class="text-3xl mb-2">📄</div>
 <span class="text-sm font-medium"
 style="color: {{ $template->primary_color }};">{{ $template->name }}</span>
 @if($template->is_default)
 <span
 class="ml-2 text-xs bg-brand-100 text-brand-700 px-2 py-1 rounded-full">{{ __('Default') }}</span>
 @endif
 </div>
 </div>
 <div class="p-4">
 <div class="text-sm text-txmain mb-4">
 <p>{{ __('Color') }}: <span class="inline-block w-4 h-4 rounded"
 style="background: {{ $template->primary_color }};"></span> {{ $template->primary_color }}
 </p>
 <p>{{ __('Font') }}: {{ __(ucfirst($template->font_family)) }}</p>
 <p>{{ __('Logo') }}: {{ __(ucfirst($template->logo_position)) }}</p>
 </div>
 <div class="flex justify-between">
 <a href="{{ route('invoicemaker.templates.builder.edit', $template) }}"
 class="text-brand-600 hover:text-brand-700 text-sm font-medium">{{ __('Edit') }}</a>
 @if(!$template->is_default)
 <button wire:click="setDefault({{ $template->id }})"
 class="text-green-600 hover:text-green-700 text-sm font-medium">{{ __('Set Default') }}</button>
 @endif
 </div>
 </div>
 </div>
 @empty
 <div class="col-span-3 text-center py-12 text-gray-500">
 {{ __('No templates found.') }}
 </div>
 @endforelse
 </div>
</div>