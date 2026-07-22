<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Time Report') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background-color: #f1f5f9; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ __('Time Report') }} — {{ $start->format('Y-m-d') }} / {{ $end->format('Y-m-d') }}</h1>
    <p><strong>{{ __('Total Hours') }}:</strong> {{ number_format($totalMinutes / 60, 2) }} h</p>

    <table>
        <thead>
            <tr><th>{{ __('Employee') }}</th><th>{{ __('Date') }}</th><th>{{ __('Start') }}</th><th>{{ __('End') }}</th><th class="right">{{ __('Duration') }}</th></tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->date->format('Y-m-d') }}</td>
                    <td>{{ $item->start_time }}</td>
                    <td>{{ $item->end_time ?? '-' }}</td>
                    <td class="right">{{ $item->durationMinutes() ? number_format($item->durationMinutes() / 60, 2) : '-' }} h</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
