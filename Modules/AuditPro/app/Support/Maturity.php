<?php

namespace Modules\AuditPro\Support;

class Maturity
{
    public static function label(float $score): string
    {
        return match (true) {
            $score >= 4.5 => 'Excellent',
            $score >= 3.5 => 'Strong',
            $score >= 2.5 => 'Solid',
            $score >= 1.5 => 'Weak',
            default => 'Critical',
        };
    }
}
