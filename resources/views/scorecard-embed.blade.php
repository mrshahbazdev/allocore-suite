<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Allocore Score') }} — {{ $team->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #fff;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 160px;
            padding: 16px;
        }
        .widget {
            width: 100%;
            max-width: 260px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .name { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; }
        .score-row { display: flex; align-items: baseline; justify-content: center; gap: 6px; margin-bottom: 6px; }
        .score { font-size: 42px; font-weight: 800; line-height: 1; }
        .out-of { font-size: 13px; color: #64748b; }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 999px;
            background: {{ config('app.team_branding.primary_color') ?? '#4f46e5' }};
            color: #fff;
        }
        .footer { margin-top: 10px; font-size: 11px; color: #94a3b8; }
        .footer a { color: {{ config('app.team_branding.primary_color') ?? '#4f46e5' }}; text-decoration: none; }
    </style>
</head>
<body>
    <div class="widget">
        <div class="name">{{ $team->name }}</div>
        <div class="score-row">
            <span class="score">{{ $score->score }}</span>
            <span class="out-of">/ 100</span>
        </div>
        <span class="badge">{{ __($score->maturity_level) }}</span>
        <div class="footer">
            <a href="{{ route('scorecard.public', $team->public_score_slug) }}" target="_blank">{{ __('Allocore Score') }}</a>
        </div>
    </div>
</body>
</html>
