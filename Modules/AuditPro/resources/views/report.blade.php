<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Audit report') }} — {{ $audit->team->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #1e293b; background: #f1f5f9; font: 14px/1.5 Arial, sans-serif; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; padding: 12px 32px; background: #0f172a; color: #cbd5e1; }
        .toolbar button { border: 0; border-radius: 7px; padding: 9px 16px; background: #4f46e5; color: white; font-weight: 700; cursor: pointer; }
        .page { width: 900px; margin: 24px auto; background: white; box-shadow: 0 10px 30px rgba(15, 23, 42, .12); }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 36px 42px; background: #4f46e5; color: white; }
        .header h1 { margin: 4px 0 0; font-size: 28px; }
        .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; opacity: .8; }
        .score { text-align: right; }
        .score strong { display: block; font-size: 42px; line-height: 1; }
        .meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 20px 42px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .meta span { display: block; color: #64748b; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .meta strong { display: block; margin-top: 3px; }
        .content { padding: 34px 42px; }
        h2 { margin: 0 0 16px; font-size: 14px; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        th { padding: 10px 12px; background: #f1f5f9; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; }
        td { padding: 14px 12px; border-bottom: 1px solid #e2e8f0; }
        .bar { width: 100%; height: 8px; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
        .bar div { height: 100%; border-radius: 999px; background: #4f46e5; }
        .recommendations { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .recommendation { border: 1px solid #fde68a; border-radius: 10px; background: #fffbeb; padding: 14px; color: #92400e; }
        .recommendation strong { display: block; margin-bottom: 4px; }
        .footer { display: flex; justify-content: space-between; padding: 20px 42px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 11px; }
        @media print {
            body { background: white; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .toolbar { display: none; }
            .page { width: auto; margin: 0; box-shadow: none; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>{{ __('AuditPro report preview') }}</span>
        <div class="flex items-center gap-2">
            <a href="{{ route('audit.report.download', $audit) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Download PDF') }}</a>
            <button onclick="window.print()">{{ __('Print or save as PDF') }}</button>
        </div>
    </div>

    <article class="page">
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
            <div><span>{{ __('Audit date') }}</span><strong>{{ $audit->updated_at->format('d M Y') }}</strong></div>
            <div><span>{{ __('Performed by') }}</span><strong>{{ $audit->creator?->name ?? __('Deleted user') }}</strong></div>
            <div><span>{{ __('Industry') }}</span><strong>{{ $audit->team->industry ?? '—' }}</strong></div>
            <div><span>{{ __('Maturity') }}</span><strong>{{ __($overallMaturity) }}</strong></div>
        </div>

        <main class="content">
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
    </article>
</body>
</html>
