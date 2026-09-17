<?php

namespace App\Services;

class QuestionToolGuesser
{
    /**
     * Keyword-based mapping from audit question text to a recommended Allocore module.
     */
    /**
     * Keyword-based mapping from audit question text to a recommended Allocore module.
     * Ordered strictly by specificity so specialized modules match before generic ones.
     */
    protected static array $patterns = [
        'lead-quality' => [
            '\bleados\b', '\bleads?\b', '\bprospects?\b', '\bconversions?\b', '\bfunnel\b', '\bsales\b',
            '\bcustomer acquisition\b', '\binteressent(en)?\b', '\banfragen?\b', '\babschlussquote\b',
            '\bvertrieb\b', '\bverkaufsabschluss\b', '\blead-qualität\b', '\bneukundengewinnung\b',
            '\bconverted into customers\b', '\bshare of leads\b'
        ],
        'invoice-maker' => [
            '\bpayment and cooperation obligations\b', '\binvoice\b', '\bpayment\b', '\boverdue\b',
            '\bdso\b', '\bdays sales outstanding\b', '\brechnung\b', '\bmahn\b', '\bforderung\b',
            '\bzahlungsziel\b', '\bzahlungsverzug\b', '\bmahnwesen\b', '\boffene rechnungen\b'
        ],
        'plan-hive' => [
            '\bservices/deliveries are provided\b', '\bdeliver(y|ies)\b', '\bproject\b', '\btask\b',
            '\bdeadline\b', '\bhandoff\b', '\bworkflow\b', '\bprojekt\b', '\baufgabe\b', '\blieferung\b',
            '\bleistungserbringung\b', '\btermin\b', '\bmeilenstein\b'
        ],
        'keyword-cluster' => [
            '\bprospects are reached\b', '\breichweite\b', '\bsichtbarkeit\b', '\bkeyword\b', '\bcontent\b',
            '\bseo\b', '\bsearch\b', '\bcluster\b', '\bcampaign\b', '\bmarketing\b', '\bsuchbegriff\b',
            '\bzielkunden erreicht\b', '\bzielgruppe\b'
        ],
        'sweet-spot' => [
            '\bsweet\s*spot\b', '\brepeat purchases\b', '\bretention\b', '\brepeat\b', '\breferral\b',
            '\badvocat', '\bsatisfaction\b', '\bloyalty\b', '\bkunde\b', '\bempfehlung\b',
            '\bzufriedenheit\b', '\bwiederkauf\b', '\bstammkunde\b', '\bweiterempfehl'
        ],
        'time-butler' => [
            '\btime\b', '\bvacation\b', '\babsence\b', '\battendance\b', '\bworking hours\b',
            '\barbeitszeit\b', '\burlaub\b', '\bzeiterfassung\b', '\babwesenheit\b'
        ],
        'focus-matrix' => [
            '\bfocus\b', '\btriage\b', '\bdelegate\b', '\bproductivity\b', '\bpriorit',
            '\bdelegier\b', '\bfokus\b', '\beisenhower\b', '\bselbstorganisation\b', '\bproblems independently\b'
        ],
        'loop-engine' => [
            '\bbottlenecks and waste\b', '\bbottleneck\b', '\bwaste\b', '\bchecklist\b',
            '\bstandard\b', '\bprocedure\b', '\bprozess\b', '\bengpass\b', '\bverschwendung\b'
        ],
        'sop-builder' => [
            '\bsop\b', '\bprocesses function even when\b', '\bkey individuals are absent\b',
            '\bstellvertreter\b', '\bstandard operating procedure\b', '\bprozessdokumentation\b',
            '\bhandbuch\b', '\banschulung\b'
        ],
        'org-matrix' => [
            '\brole\b', '\bcompetenc', '\bstrength\b', '\bpeople\b', '\bteam\b', '\borgani',
            '\bsuccession\b', '\bbackup\b', '\bmitarbeiter\b', '\bstelle\b', '\borganigramm\b',
            '\bleadership transitions\b', '\bnachfolge\b'
        ],
        'cash-core' => [
            '\bliabilities\b', '\bdebt\b', '\bliquidity reserves\b', '\breserves\b', '\breserve\b',
            '\bcash\b', '\bprofit\b', '\bliquidity\b', '\bfinancing\b', '\binvestment\b',
            '\broi\b', '\bbreak-even\b', '\bliquidität\b', '\bgewinn\b', '\bschulden\b',
            '\bverbindlichkeiten\b', '\brücklage\b', '\brunway\b'
        ],
        'financial-platform' => [
            '\bcontribution margin\b', '\bdeckungsbeitrag\b', '\bmargin\b', '\bmarge\b',
            '\brentabilität\b', '\bfinanzanalyse\b', '\bprofitabilität\b'
        ],
        'vision-flow' => [
            '\bvision\b', '\bmission\b', '\bpurpose\b', '\bvalues\b', '\bstrategy\b',
            '\bstrategie\b', '\bwerte\b', '\bunternehmensziel\b', '\bleitbild\b', '\bsinn\b',
            '\bconviction\b', '\büberzeugung\b'
        ],
        'nur-du' => [
            '\balignment\b', '\bquarterly\b', '\bonboarding\b', '\bpersonal goal',
            '\bquartalsziel\b', '\bpersönliche ziele\b', '\bvisionsabgleich\b'
        ],
        'knowledge-manager' => [
            '\bknowledge\b', '\bdocument\b', '\bhandbook\b', '\bmanual\b', '\blearn\b',
            '\btraining\b', '\bwiki\b', '\bwissen\b', '\bdokumentation\b', '\blernende organisation\b',
            '\bcontinuously learns\b'
        ],
        'kpi-tool' => [
            '\bkpi\b', '\bkey performance indicator\b', '\btarget\b', '\bmetric\b', '\bkennzahl\b'
        ],
        'revenue-planner' => [
            '\brequired monthly revenue\b', '\bmonthly revenue\b', '\bmonatliche[rn]? umsatz\b',
            '\bmindestumsatz\b', '\bplanned revenue\b', '\brevenue goal\b', '\bumsatzziel\b',
            '\bfixkosten\b', '\brevenue plan\b', '\bumsatzplan\b', '\bumsatz\b', '\bturnover\b'
        ],
    ];

    protected static array $pillarFallbacks = [
        'Revenue' => 'revenue-planner',
        'Profit' => 'cash-core',
        'Order' => 'plan-hive',
        'Influence' => 'keyword-cluster',
        'Legacy' => 'vision-flow',
    ];

    public static function guess(string $questionText, ?string $pillar = null): ?string
    {
        $haystack = mb_strtolower($questionText);

        foreach (static::$patterns as $moduleKey => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match('/'.$pattern.'/iu', $haystack)) {
                    return $moduleKey;
                }
            }
        }

        return static::$pillarFallbacks[$pillar] ?? null;
    }

    public static function guessKnowledgeSlug(string $questionText, ?string $pillar = null): ?string
    {
        $haystack = mb_strtolower($questionText);

        if (preg_match('/(fixkosten|fixed cost|kostenstruktur|cost structure)/iu', $haystack)) {
            return 'fixkosten';
        }

        if (preg_match('/(umsatz|revenue|turnover|mindestumsatz)/iu', $haystack)) {
            return 'revenue';
        }

        if (preg_match('/(lead|interessent|conversion|prospect)/iu', $haystack)) {
            return 'lead-quality';
        }

        $map = [
            'Revenue' => 'revenue',
            'Profit' => 'profitability',
            'Order' => 'operations',
            'Influence' => 'market-influence',
            'Legacy' => 'organizational-legacy',
        ];

        return $map[$pillar] ?? null;
    }
}
