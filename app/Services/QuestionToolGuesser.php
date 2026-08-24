<?php

namespace App\Services;

class QuestionToolGuesser
{
    /**
     * Keyword-based mapping from audit question text to a recommended Allocore module.
     */
    protected static array $patterns = [
        'lead-quality' => ['\blead\b', '\bprospect\b', '\bconversion\b', '\bfunnel\b', '\bsales\b', '\bcustomer acquisition\b'],
        'invoice-maker' => ['\binvoice\b', '\bpayment\b', '\boverdue\b', '\bdso\b', '\bdays sales outstanding\b'],
        'kpi-tool' => ['\bkpi\b', '\bkey performance indicator\b', '\btarget\b', '\bmetric\b'],
        'cash-core' => ['\bcash\b', '\bprofit\b', '\bmargin\b', '\bliquidity\b', '\breserve\b', '\bdebt\b', '\bfinancing\b', '\binvestment\b', '\broi\b', '\bbreak-even\b'],
        'time-butler' => ['\btime\b', '\bvacation\b', '\babsence\b', '\battendance\b', '\bworking hours\b'],
        'focus-matrix' => ['\bfocus\b', '\btriage\b', '\bdelegate\b', '\bdelegate\b', '\bproductivity\b', '\bpriorit'],
        'plan-hive' => ['\bproject\b', '\btask\b', '\bdelivery\b', '\bdeadline\b', '\bhandoff\b', '\bworkflow\b'],
        'loop-engine' => ['\bprocess\b', '\bsop\b', '\bchecklist\b', '\bstandard\b', '\bbottleneck\b', '\bwaste\b', '\bprocedure\b'],
        'org-matrix' => ['\brole\b', '\bcompetenc', '\bstrength\b', '\bpeople\b', '\bteam\b', '\borgani', '\bsuccession\b', '\bbackup\b'],
        'sweet-spot' => ['\bcustomer\b', '\bretention\b', '\brepeat\b', '\breferral\b', '\badvocat', '\bsatisfaction\b', '\bloyalty\b'],
        'keyword-cluster' => ['\bkeyword\b', '\bcontent\b', '\bseo\b', '\bsearch\b', '\bcluster\b', '\bcampaign\b', '\bmarketing\b'],
        'vision-flow' => ['\bvision\b', '\bmission\b', '\bpurpose\b', '\bvalues\b', '\bgoal\b', '\bstrategy\b'],
        'nur-du' => ['\balignment\b', '\bquarterly\b', '\bonboarding\b', '\bdevelop\b', '\bpersonal goal'],
        'knowledge-manager' => ['\bknowledge\b', '\bdocument\b', '\bhandbook\b', '\bmanual\b', '\blearn\b', '\btraining\b', '\bwiki\b'],
    ];

    protected static array $pillarFallbacks = [
        'Revenue' => 'lead-quality',
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
