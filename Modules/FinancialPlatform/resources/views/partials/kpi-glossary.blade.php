@php
    $definitions = [
        'EBIT' => 'Ergebnis vor Zinsen und Steuern. Zeigt, wie profitabel das operative Geschaeft ohne Finanzierung und Steuern ist.',
        'EBITDA' => 'EBIT plus Abschreibungen. Hilft, die operative Leistungsfaehigkeit unabhaengig von Bilanzierungs- und Investitionseffekten zu sehen.',
        'Current Ratio' => 'Umlaufvermoegen geteilt durch kurzfristige Verbindlichkeiten. Misst, ob kurzfristige Schulden bezahlt werden koennen.',
        'Debt/Equity Ratio' => 'Gesamtschulden geteilt durch Eigenkapital. Zeigt, wie stark ein Unternehmen fremdfinanziert ist.',
        'LTV/CAC Ratio' => 'Verhaeltnis von Kundenwert zu Kundenakquisekosten. Hoeher bedeutet meist effizienteres Wachstum.',
        'Runway' => 'Wie viele Monate das vorhandene Cash bei aktuellem Cashburn reicht.',
        'ROE' => 'Eigenkapitalrendite. Jahresueberschuss im Verhaeltnis zum Eigenkapital.',
        'ROA' => 'Gesamtkapitalrendite. Jahresueberschuss im Verhaeltnis zur Bilanzsumme.',
        'DSO' => 'Days Sales Outstanding (Debitorenlaufzeit). Durchschnittliche Tage bis offene Kundenrechnungen bezahlt werden.',
        'DPO' => 'Days Payables Outstanding (Kreditorenlaufzeit). Durchschnittliche Tage bis Lieferantenrechnungen bezahlt werden.',
        'Accounts receivable turnover' => 'Umschlagshaeufigkeit der Forderungen. Zeigt, wie oft Forderungen pro Jahr in Cash umgewandelt werden.',
        'DSCR' => 'Debt Service Coverage Ratio. Verhaeltnis von verfuegbarem Ertrag zu Schuldendienst.',
        'LTV' => 'Loan-to-Value. Darlehen im Verhaeltnis zum Immobilienwert oder Kaufpreis.',
    ];
@endphp

<div class="card" style="margin-top:16px;">
    <div class="card-title">📘 Glossar (einfach erklaert)</div>
    <div style="display:grid; gap:10px;">
        @foreach($definitions as $term => $text)
        <div style="border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
            <div style="font-size:13px; color:#c7d2fe; font-weight:600;">{{ $term }}</div>
            <div style="font-size:12px; color:#94a3b8; margin-top:3px; line-height:1.6;">{{ $text }}</div>
        </div>
        @endforeach
    </div>
</div>
