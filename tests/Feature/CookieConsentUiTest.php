<?php

namespace Tests\Feature;

use App\Support\ComplianceCookieBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentUiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        ComplianceCookieBanner::forceMode(null);
        parent::tearDown();
    }

    public function test_homepage_shows_informative_notice_by_default_mode(): void
    {
        ComplianceCookieBanner::forceMode(ComplianceCookieBanner::MODE_INFORMATIVE);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="cookie-informative-banner"', false)
            ->assertSee('Мы используем файлы cookie для улучшения работы сайта', false)
            ->assertSee('Подробнее', false)
            ->assertDontSee('id="cookie-consent-banner"', false);
    }

    public function test_homepage_shows_full_consent_banner_in_consent_mode(): void
    {
        ComplianceCookieBanner::forceMode(ComplianceCookieBanner::MODE_CONSENT);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="cookie-consent-banner"', false)
            ->assertSee('Принять и продолжить', false)
            ->assertDontSee('id="cookie-informative-banner"', false);
    }

    public function test_homepage_hides_all_banners_in_off_mode(): void
    {
        ComplianceCookieBanner::forceMode(ComplianceCookieBanner::MODE_OFF);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="cookie-informative-banner"', false)
            ->assertDontSee('id="cookie-consent-banner"', false);
    }

    public function test_cookie_policy_describes_metrika_without_ads_wording(): void
    {
        $this->get(route('legal.cookies'))
            ->assertOk()
            ->assertSee('Статистика посещений', false)
            ->assertSee('вебвизор', false)
            ->assertDontSee('рекламные пиксели', false);
    }
}
