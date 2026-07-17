<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Audit report') }} — {{ $audit->team->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #1e293b; background: #fff; font: 14px/1.5 Arial, sans-serif; }
        .page { width: 100%; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 24px; background: #4f46e5; color: white; border-radius: 8px; }
        .header h1 { margin: 4px 0 0; font-size: 24px; }
        .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; opacity: .8; }
        .score { text-align: right; }
        .score strong { display: block; font-size: 36px; line-height: 1; }
        .meta { display: table; width: 100%; margin: 20px 0; padding: 16px 0; background: #f8fafc; border-radius: 8px; }
        .meta-cell { display: table-cell; width: 25%; padding: 0 16px; border-right: 1px solid #e2e8f0; }
        .meta-cell:last-child { border-right: 0; }
        .meta-cell span { display: block; color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .meta-cell strong { display: block; margin-top: 3px; }
        h2 { margin: 24px 0 12px; font-size: 13px; text-transform: uppercase; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { padding: 10px 12px; background: #f1f5f9; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .bar { width: 100%; height: 8px; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
        .bar div { height: 100%; border-radius: 999px; background: #4f46e5; }
        .recommendations { margin-top: 16px; }
        .recommendation { border: 1px solid #fde68a; border-radius: 8px; background: #fffbeb; padding: 12px; margin-bottom: 10px; color: #92400e; }
        .recommendation strong { display: block; margin-bottom: 4px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 11px; display: flex; justify-content: space-between; }
        @page { margin: 15mm; }
    </style>
</head>
<body>
    <div class="page">
        <header class="header">
            <div>
                <div class="eyebrow">{{ __('Business maturity audit') }}</div>
                <h1>{{ $audit->team->name }}</h1>
                <div>{{ $audit->template?->name }}</div>
            </div>
            <div class="score">
                <strong>{{ number_format($overallScore, 1) }}</strong>
                <span>{{ __('Overall score out of 5') }}</span>
            </div>
        </header>

        <div class="meta">
            <div class="meta-cell"><span>{{ __('Audit date') }}</span><strong>{{ $audit->updated_at->format('d M Y') }}</strong></div>
            <div class="meta-cell"><span>{{ __('Performed by') }}</span><strong>{{ $audit->creator?->name ?? __('Deleted user') }}</strong></div>
            <div class="meta-cell"><span>{{ __('Industry') }}</span><strong>{{ $audit->team->industry ?? '—' }}</strong></div>
            <div class="meta-cell"><span>{{ __('Maturity') }}</span><strong>{{ __($overallMaturity) }}</strong></div>
        </div>

        <main>
            <h2>{{ __('Pillar breakdown') }}</h2>
            <table>
                <thead><tr><th>{{ __('Pillar') }}</th><th style="width:45%">{{ __('Progress') }}</th><th>{{ __('Score') }}</th><th>{{ __('Level') }}</th></tr></thead>
                <tbody>
                    @foreach ($audit->results as $result)
                        <tr>
                            <td><strong>{{ $result->level }}</strong></td>
                            <td><div class="bar"><div style="width: {{ min(100, ((float) $result->average_score / 5) * 100) }}%"></div></div></td>
                            <td>{{ number_format((float) $result->average_score, 1) }}/5</td>
                            <td>{{ __($result->maturity_level) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2>{{ __('Priority recommendations') }}</h2>
            <div class="recommendations">
                @foreach ($audit->results->sortBy('average_score')->take(3) as $result)
                    <div class="recommendation">
                        <strong>{{ $result->level }}</strong>
                        {{ __('Create a focused improvement plan for this pillar and review progress during the next audit cycle.') }}
                    </div>
                @endforeach
            </div>
        </main>

        <footer class="footer">
            <span>{{ __('AuditPro — Allocore Suite') }}</span>
            <span>{{ __('Confidential') }} · {{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>
