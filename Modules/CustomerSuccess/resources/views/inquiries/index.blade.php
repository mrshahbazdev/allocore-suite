@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Inquiries') }}</h1>
        <a href="{{ route('customersuccess.inquiries.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Ask Question') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($inquiries->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No inquiries yet.') }}</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($inquiries as $inquiry)
                    <li class="py-4">
                        <a href="{{ route('customersuccess.inquiries.show', $inquiry) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $inquiry->question }}</a>
                        <p class="mt-1 text-xs text-slate-500">{{ $inquiry->priority ? __($inquiry->priority) : '-' }} &middot; {{ $inquiry->created_at->diffForHumans() }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $inquiries->links() }}</div>
        @endif
    </div>
</div>
@endsection
