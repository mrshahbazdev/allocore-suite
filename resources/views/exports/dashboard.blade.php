<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Allocore Suite — Dashboard Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #334155; }
        h1 { font-size: 22px; color: #1e293b; margin-bottom: 4px; }
        h2 { font-size: 14px; color: #475569; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; color: #334155; font-weight: 600; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-locked { background: #f1f5f9; color: #475569; }
        .grid { display: table; width: 100%; margin-bottom: 12px; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; width: 25%; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .stat-value { font-size: 18px; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 10px; text-transform: uppercase; color: #64748b; }
    </style>
</head>
<body>
    <h1>Allocore Suite Dashboard</h1>
    <p class="meta">{{ $user->name }} · {{ $user->email }} · {{ now()->format('F j, Y') }}</p>

    <div class="grid">
        <div class="grid-row">
            <div class="grid-cell"><div class="stat-label">Active tools</div><div class="stat-value">{{ $stats['active_modules'] }}</div></div>
            <div class="grid-cell"><div class="stat-label">Locked add-ons</div><div class="stat-value">{{ $stats['locked_modules'] }}</div></div>
            <div class="grid-cell"><div class="stat-label">Total tools</div><div class="stat-value">{{ $stats['total_modules'] }}</div></div>
            <div class="grid-cell"><div class="stat-label">Workspace members</div><div class="stat-value">{{ $stats['workspace_members'] }}</div></div>
        </div>
    </div>

    @if ($subscription)
        <p><strong>Current plan:</strong> {{ $subscription->plan?->name ?? __('Free') }}
        @if ($subscription->ends_at)
            · <strong>Renews:</strong> {{ $subscription->ends_at->format('F j, Y') }}
        @endif
        </p>
    @endif

    <h2>Active Tools</h2>
    @if ($activeModules->isEmpty())
        <p>No active tools yet.</p>
    @else
        <table>
            <thead><tr><th>Tool</th><th>Description</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($activeModules as $module)
                    <tr>
                        <td>{{ $module->name }}</td>
                        <td>{{ $module->description }}</td>
                        <td><span class="badge badge-active">Active</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Available Add-ons</h2>
    @if ($lockedModules->isEmpty())
        <p>All available tools are active.</p>
    @else
        <table>
            <thead><tr><th>Tool</th><th>Description</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($lockedModules as $module)
                    <tr>
                        <td>{{ $module->name }}</td>
                        <td>{{ $module->description }}</td>
                        <td><span class="badge badge-locked">Locked</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Recent Activity</h2>
    @if ($activityLogs->isEmpty())
        <p>No recent activity recorded.</p>
    @else
        <table>
            <thead><tr><th>Event</th><th>Time</th></tr></thead>
            <tbody>
                @foreach ($activityLogs as $log)
                    <tr>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
