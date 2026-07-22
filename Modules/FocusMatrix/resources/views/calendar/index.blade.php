@extends('layouts.shell', ['title' => __('Calendar')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Calendar') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Event') }}</h2>
            <span class="text-sm text-slate-500">{{ $anchor->format('F Y') }}</span>
        </div>
        <form method="POST" action="{{ route('focusmatrix.calendar.events.store') }}" class="grid md:grid-cols-4 gap-4">
            @csrf
            <input type="text" name="title" placeholder="{{ __('Title') }}" class="rounded-lg border-slate-300 shadow-sm" required>
            <input type="datetime-local" name="starts_at" class="rounded-lg border-slate-300 shadow-sm" required>
            <input type="datetime-local" name="ends_at" class="rounded-lg border-slate-300 shadow-sm" required>
            <select name="color" class="rounded-lg border-slate-300 shadow-sm">
                @foreach (Modules\FocusMatrix\Models\CalendarEvent::COLORS as $color)
                    <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 md:col-span-4 text-sm"><input type="checkbox" name="all_day" value="1" class="rounded border-slate-300"> {{ __('All day') }}</label>
            <div class="md:col-span-4">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Event') }}</button>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('focusmatrix.calendar.index', ['month' => $anchor->copy()->subMonth()->format('Y-m')]) }}" class="text-indigo-600 hover:underline">« {{ __('Previous') }}</a>
        <a href="{{ route('focusmatrix.calendar.index', ['month' => $anchor->copy()->addMonth()->format('Y-m')]) }}" class="text-indigo-600 hover:underline">{{ __('Next') }} »</a>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Native Events') }}</h2>
            @if ($nativeEvents->isEmpty() && $taskDue->isEmpty() && $focusBlocks->isEmpty() && $googleEvents->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No events this month.') }}</p>
            @else
                <ul class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    @foreach ($nativeEvents as $event)
                        <li class="py-2 text-sm flex justify-between">
                            <span><span class="inline-block w-2 h-2 rounded-full bg-{{ $event->color }} mr-2"></span>{{ $event->title }}</span>
                            <span class="text-slate-500">{{ $event->starts_at->format('m-d H:i') }}</span>
                        </li>
                    @endforeach
                    @foreach ($taskDue as $t)
                        <li class="py-2 text-sm flex justify-between">
                            <span>{{ __('Due') }}: {{ $t->title }}</span>
                            <span class="text-slate-500">{{ $t->due_at->format('m-d H:i') }}</span>
                        </li>
                    @endforeach
                    @foreach ($focusBlocks as $t)
                        <li class="py-2 text-sm flex justify-between">
                            <span>{{ __('Focus') }}: {{ $t->title }}</span>
                            <span class="text-slate-500">{{ $t->focused_block_at->format('m-d H:i') }}</span>
                        </li>
                    @endforeach
                    @foreach ($googleEvents as $e)
                        <li class="py-2 text-sm flex justify-between">
                            <span>{{ $e['title'] }}</span>
                            <span class="text-slate-500">{{ \Illuminate\Support\Carbon::parse($e['starts_at'])->format('m-d H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Google Calendar') }}</h2>
            @if ($connected)
                <p class="text-sm text-emerald-700 mb-4">{{ __('Connected') }}: {{ $account_email }}</p>
                @if (count($weak_meetings))
                    <form method="POST" action="{{ route('focusmatrix.calendar.import-weak') }}">
                        @csrf
                        <button class="mb-4 rounded-lg bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Import weak meetings') }}</button>
                    </form>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($weak_meetings as $m)
                            <li class="py-2">{{ $m['title'] }} — {{ implode(', ', $m['flags']) }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">{{ __('No flagged meetings found.') }}</p>
                @endif
            @else
                <p class="text-sm text-slate-500 mb-4">{{ __('Connect Google Calendar to see events and weak-meeting audit.') }}</p>
                <a href="{{ route('focusmatrix.integrations.google.connect') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Connect Google') }}</a>
            @endif
        </div>
    </div>
</div>
@endsection
