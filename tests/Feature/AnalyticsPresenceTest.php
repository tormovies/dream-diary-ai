<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ComplianceCookieBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsPresenceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        ComplianceCookieBanner::forceMode(null);
        parent::tearDown();
    }

    public function test_public_pages_load_metrika_immediately_in_informative_mode(): void
    {
        ComplianceCookieBanner::forceMode(ComplianceCookieBanner::MODE_INFORMATIVE);

        $this->get('/')
            ->assertOk()
            ->assertSee('mc.yandex.ru/metrika/tag.js', false)
            ->assertSee('id="cookie-informative-banner"', false)
            ->assertDontSee('name="deferred-metrika-id"', false)
            ->assertDontSee('id="cookie-consent-banner"', false);
    }

    public function test_public_pages_defer_metrika_in_consent_mode(): void
    {
        ComplianceCookieBanner::forceMode(ComplianceCookieBanner::MODE_CONSENT);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="deferred-metrika-id"', false)
            ->assertDontSee('mc.yandex.ru/metrika/tag.js', false)
            ->assertSee('id="cookie-consent-banner"', false);
    }

    public function test_public_pages_have_no_banner_in_off_mode(): void
    {
        ComplianceCookieBanner::forceMode(ComplianceCookieBanner::MODE_OFF);

        $this->get('/')
            ->assertOk()
            ->assertSee('mc.yandex.ru/metrika/tag.js', false)
            ->assertDontSee('id="cookie-informative-banner"', false)
            ->assertDontSee('id="cookie-consent-banner"', false);
    }

    public function test_admin_pages_load_metrika_immediately(): void
    {
        $admin = User::factory()->create([
            'nickname' => 'adminuser',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('mc.yandex.ru/metrika/tag.js', false)
            ->assertDontSee('name="deferred-metrika-id"', false);
    }
}
