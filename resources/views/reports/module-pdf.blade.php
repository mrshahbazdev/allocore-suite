<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $module->name }} — {{ __('Report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #334155; }
        h1 { font-size: 22px; color: #1e293b; margin-bottom: 4px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; color: #334155; font-weight: 600; }
    </style>
</head>
<body>
    <h1>{{ $module->name }}</h1>
    <p class="meta">{{ $generatedAt->format('F j, Y H:i') }}</p>

    <table>
        <tbody>
            <tr><th>{{ __('Key') }}</th><td>{{ $module->key }}</td></tr>
            <tr><th>{{ __('Description') }}</th><td>{{ $module->description }}</td></tr>
            <tr><th>{{ __('Subscribed') }}</th><td>{{ $stats['accessible'] ? __('Yes') : __('No') }}</td></tr>
            <tr><th>{{ __('Primary resource') }}</th><td>{{ $stats['primary_resource'] }}</td></tr>
            <tr><th>{{ __('Record count') }}</th><td>{{ $stats['primary_resource_count'] }}</td></tr>
        </tbody>
    </table>
</body>
</html>
