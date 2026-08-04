@props(['title', 'value', 'icon', 'color'])

<div class="bg-card rounded-lg shadow p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 uppercase tracking-widest">{{ $title }}</p>
 <p class="text-2xl font-extrabold text-txmain mt-1">{{ $value }}</p>
 </div>
 <div class="p-3 rounded-xl bg-{{ $color }}-50 border border-{{ $color }}-100 shadow-sm text-{{ $color }}-600">
 {!! $icon !!}
 </div>
 </div>
</div>