<?php

namespace Tests\Unit;

use App\Support\ComplianceCookieBanner;
use Tests\TestCase;

class ComplianceCookieBannerTest extends TestCase
{
    public function test_default_mode_is_informative(): void
    {
        $this->assertSame(ComplianceCookieBanner::MODE_INFORMATIVE, ComplianceCookieBanner::mode());
    }
}
