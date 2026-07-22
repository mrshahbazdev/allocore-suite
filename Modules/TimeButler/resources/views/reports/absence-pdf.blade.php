<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Absence Report') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background-color: #f1f5f9; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ __('Absence Report') }} — {{ $start->format('Y-m-d') }} / {{ $end->format('Y-m-d') }}</h1>

    <table>
        <thead>
            <tr><th>{{ __('Type') }}</th><th class="right">{{ __('Days') }}</th><th class="right">{{ __('Requests') }}</th></tr>
        </thead>
        <tbody>
            @foreach ($summary as $row)
                <tr><td>{{ $row['type'] }}</td><td class="right">{{ number_format($row['days'], 1) }}</td><td class="right">{{ $row['count'] }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <thead>
            <tr><th>{{ __('Employee') }}</th><th>{{ __('Type') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th class="right">{{ __('Days') }}</th></tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr><td>{{ $item->user->name }}</td><td>{{ $item->absenceType->name }}</td><td>{{ $item->start_date->format('Y-m-d') }}</td><td>{{ $item->end_date->format('Y-m-d') }}</td><td class="right">{{ number_format($item->total_days, 1) }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
