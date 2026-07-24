@if ($activityLogs->isEmpty())
    <p class="text-sm text-slate-500">{{ __('No recent activity recorded.') }}</p>
@else
    <ul class="divide-y divide-slate-100">
        @foreach ($activityLogs as $log)
            <li class="py-3">
                <p class="text-sm text-slate-900">{{ $log->description }}</p>
                <p class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</p>
            </li>
        @endforeach
    </ul>
@endif
