<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_hides_cookie_banner_when_disabled(): void
    {
        config(['compliance.cookie_banner_enabled' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="cookie-consent-banner"', false)
            ->assertDontSee('Принять и продолжить', false);
    }

    public function test_homepage_shows_improved_cookie_banner_markup_when_enabled(): void
    {
        config(['compliance.cookie_banner_enabled' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="cookie-btn-accept"', false)
            ->assertSee('Принять и продолжить', false)
            ->assertSee('Без аналитики', false)
            ->assertSee('Статистика посещений', false)
            ->assertDontSee('рекламные пиксели', false);
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
