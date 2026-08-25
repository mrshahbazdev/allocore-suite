<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizationDefaultsTest extends TestCase
{
    public function test_german_is_the_default_locale_configuration(): void
    {
        $this->assertSame('de', config('app.locale'));
        $this->assertSame('de', config('app.fallback_locale'));
    }

    public function test_audit_frequency_strings_have_german_translations(): void
    {
        $this->assertSame('Großes Audit — alle 4 Wochen', __('Major audit — every 4 weeks', [], 'de'));
        $this->assertSame('alle 4 Wochen', __('every 4 weeks', [], 'de'));
    }
}
