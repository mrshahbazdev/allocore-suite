<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Beispiel-Allocore-Audit') }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #334155; padding: 40px; }
        h1 { font-size: 28px; color: #0f172a; margin-bottom: 8px; }
        h2 { font-size: 18px; color: #0f172a; margin-top: 32px; margin-bottom: 16px; }
        .score { font-size: 48px; font-weight: bold; color: #0f172a; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 9999px; background: #e0e7ff; color: #3730a3; font-weight: bold; font-size: 14px; }
        .pillars { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .pillars th, .pillars td { text-align: left; padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .pillars th { color: #64748b; font-size: 12px; text-transform: uppercase; }
        .bar { background: #e2e8f0; border-radius: 4px; height: 10px; width: 100%; }
        .bar-fill { background: #4f46e5; height: 10px; border-radius: 4px; }
        .recommendation { background: #fffbeb; border: 1px solid #fde68a; padding: 12px; border-radius: 8px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <h1>{{ __('Beispiel-Allocore-Audit') }}</h1>
    <p>{{ __('Musterunternehmen: Mittelständischer Dienstleister') }}</p>

    <div style="margin-top: 24px;">
        <p style="font-size: 14px; color: #64748b;">{{ __('Allocore Score') }}</p>
        <div class="score">{{ $score->score }}</div>
        <span class="badge">{{ $score->maturity_level }}</span>
    </div>

    <h2>{{ __('Pillar-Ergebnisse') }}</h2>
    <table class="pillars">
        <thead>
            <tr>
                <th>{{ __('Pillar') }}</th>
                <th>{{ __('Score') }}</th>
                <th>{{ __('Maturity') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($score->pillars as $pillar)
                <tr>
                    <td>{{ $pillar['name'] }}</td>
                    <td>
                        <div class="bar"><div class="bar-fill" style="width: {{ min(100, max(0, $pillar['score'])) }}%"></div></div>
                    </td>
                    <td>{{ $pillar['maturity'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>{{ __('Priorisierte Empfehlungen') }}</h2>
    @foreach (collect($score->pillars)->sortBy('score')->take(3) as $pillar)
        <div class="recommendation">
            <strong>{{ $pillar['name'] }} — {{ $pillar['score'] }}</strong>
            <p>{{ __('Als nächstes priorisieren: diesen Reifegrad weiter steigern.') }}</p>
        </div>
    @endforeach
</body>
</html>
