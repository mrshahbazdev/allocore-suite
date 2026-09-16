<?php

namespace App\Services;

class QuestionToolGuesser
{
    /**
     * Keyword-based mapping from audit question text to a recommended Allocore module.
     */
    protected static array $patterns = [
        'revenue-planner' => ['\bmonthly revenue\b', '\bmonatliche[rn]? umsatz\b', '\bumsatz\b', '\bturnover\b', '\bmindestumsatz\b', '\bplanned revenue\b', '\brevenue goal\b', '\bumsatzziel\b', '\bfixkosten\b', '\brevenue plan\b', '\bumsatzplan\b'],
        'lead-quality' => ['\blead\b', '\bprospect\b', '\bconversion\b', '\bfunnel\b', '\bsales\b', '\bcustomer acquisition\b', '\binteressent\b', '\banfragen\b', '\babschlussquote\b'],
        'invoice-maker' => ['\binvoice\b', '\bpayment\b', '\boverdue\b', '\bdso\b', '\bdays sales outstanding\b', '\brechnung\b', '\bmahn\b', '\bforderung\b'],
        'kpi-tool' => ['\bkpi\b', '\bkey performance indicator\b', '\btarget\b', '\bmetric\b', '\bkennzahl\b'],
        'cash-core' => ['\bcash\b', '\bprofit\b', '\bmargin\b', '\bliquidity\b', '\breserve\b', '\bdebt\b', '\bfinancing\b', '\binvestment\b', '\broi\b', '\bbreak-even\b', '\bliquidität\b', '\bgewinn\b'],
        'time-butler' => ['\btime\b', '\bvacation\b', '\babsence\b', '\battendance\b', '\bworking hours\b', '\barbeitszeit\b', '\burlaub\b'],
        'focus-matrix' => ['\bfocus\b', '\btriage\b', '\bdelegate\b', '\bproductivity\b', '\bpriorit', '\bdelegier\b', '\bfokus\b'],
        'plan-hive' => ['\bproject\b', '\btask\b', '\bdelivery\b', '\bdeadline\b', '\bhandoff\b', '\bworkflow\b', '\bprojekt\b', '\baufgabe\b'],
        'loop-engine' => ['\bprocess\b', '\bsop\b', '\bchecklist\b', '\bstandard\b', '\bbottleneck\b', '\bwaste\b', '\bprocedure\b', '\bprozess\b', '\bengpass\b'],
        'org-matrix' => ['\brole\b', '\bcompetenc', '\bstrength\b', '\bpeople\b', '\bteam\b', '\borgani', '\bsuccession\b', '\bbackup\b', '\bmitarbeiter\b', '\bstelle\b'],
        'sweet-spot' => ['\bcustomer\b', '\bretention\b', '\brepeat\b', '\breferral\b', '\badvocat', '\bsatisfaction\b', '\bloyalty\b', '\bkunde\b', '\bempfehlung\b', '\bzufriedenheit\b'],
        'keyword-cluster' => ['\bkeyword\b', '\bcontent\b', '\bseo\b', '\bsearch\b', '\bcluster\b', '\bcampaign\b', '\bmarketing\b', '\bsuchbegriff\b'],
        'vision-flow' => ['\bvision\b', '\bmission\b', '\bpurpose\b', '\bvalues\b', '\bgoal\b', '\bstrategy\b', '\bstrategie\b', '\bwerte\b', '\bunternehmensziel\b'],
        'nur-du' => ['\balignment\b', '\bquarterly\b', '\bonboarding\b', '\bdevelop\b', '\bpersonal goal', '\bquartalsziel\b'],
        'knowledge-manager' => ['\bknowledge\b', '\bdocument\b', '\bhandbook\b', '\bmanual\b', '\blearn\b', '\btraining\b', '\bwiki\b', '\bwissen\b', '\bhandbuch\b', '\bdokumentation\b'],
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
