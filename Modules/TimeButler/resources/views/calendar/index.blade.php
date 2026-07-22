@extends('layouts.shell')

@section('title', __('Team Calendar'))
@section('page-title', __('Team Calendar'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Team Calendar') }}</h1>
                <p class="text-sm text-slate-500">{{ $start->format('F Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('timebutler.calendar.index', ['year' => $start->copy()->subMonth()->year, 'month' => $start->copy()->subMonth()->month]) }}" class="rounded-lg bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Previous') }}</a>
                <a href="{{ route('timebutler.calendar.index', ['year' => now()->year, 'month' => now()->month]) }}" class="rounded-lg bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Today') }}</a>
                <a href="{{ route('timebutler.calendar.index', ['year' => $start->copy()->addMonth()->year, 'month' => $start->copy()->addMonth()->month]) }}" class="rounded-lg bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Next') }}</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-xs">
                <thead>
                    <tr>
                        <th class="sticky left-0 border-b border-r bg-slate-50 p-2 text-slate-500">{{ __('Employee') }}</th>
                        @foreach ($period as $day)
                            <th class="border-b border-r p-2 text-center {{ $day->isWeekend() || ($holidays[$day->format('Y-m-d')] ?? null) ? 'bg-slate-100 text-slate-400' : 'text-slate-700' }}" style="min-width: 2.5rem">
                                {{ $day->format('D') }}<br>{{ $day->format('d') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b">
                            <td class="sticky left-0 border-r bg-white p-2 font-medium text-slate-900">{{ $user->name }}</td>
                            @foreach ($period as $day)
                                @php
                                    $key = $day->format('Y-m-d');
                                    $req = $requests->first(fn ($r) => $r->user_id === $user->id && $day->between($r->start_date, $r->end_date) && $r->status !== 'cancelled');
                                    $holiday = $holidays[$key] ?? null;
                                    $bg = $req ? $req->absenceType->color : ($holiday ? '#f1f5f9' : 'transparent');
                                    $opacity = $req && $req->status === 'pending' ? '0.4' : '1';
                                @endphp
                                <td class="border-r p-0 text-center" title="{{ $req?->absenceType->name ?? ($holiday?->name ?? '') }}">
                                    <div class="h-full w-full py-3" style="background-color: {{ $bg }}; opacity: {{ $opacity }}"></div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-4 text-sm">
            @foreach ($requests->pluck('absenceType')->unique('id') as $type)
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $type->color }}"></span>
                    <span>{{ $type->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
