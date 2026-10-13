<?php

namespace Tests\Feature;

use App\Models\Setting;
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

    public function test_admin_can_save_and_clear_additional_head_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $code = '<script>window.extraCounter = "enabled";</script>';

        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'additional_head_code' => $code,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'additional_head_code', 'value' => $code]);
        $this->get(route('admin.settings'))->assertOk()->assertSee(e($code), false);

        $this->patch(route('admin.settings.update'), [
            'additional_head_code' => '',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseMissing('settings', ['key' => 'additional_head_code']);
        $this->get('/')->assertOk()->assertDontSee($code, false);
    }

    public function test_additional_head_code_is_rendered_raw_before_head_closes(): void
    {
        $code = '<script>window.extraCounter = "enabled";</script>';
        Setting::setValue('additional_head_code', $code);

        foreach (['/', '/login', '/register'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee($code, false);
            $this->assertMatchesRegularExpression('/'.preg_quote($code, '/').'\\s*<\\/head>/', $response->getContent());
            $this->assertSame(1, substr_count($response->getContent(), $code));
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.users'))->assertOk();
        $this->assertMatchesRegularExpression('/'.preg_quote($code, '/').'\\s*<\\/head>/', $response->getContent());
    }

    public function test_additional_head_code_preserves_entities_and_cannot_close_settings_textarea(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $code = '<script>window.extraCounter = {text: "&amp; &lt; &#34; & raw", tag: "</textarea>", comparison: 1 < 2};</script>';

        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'additional_head_code' => $code,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'additional_head_code', 'value' => $code]);
        $settings = $this->get(route('admin.settings'))->assertOk();
        $this->assertMatchesRegularExpression('/<textarea\\b[^>]*id="additional_head_code"[^>]*>'.preg_quote(e($code), '/').'<\\/textarea>/', $settings->getContent());

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$settings->getContent());
        $xpath = new \DOMXPath($document);
        $textarea = $xpath->query('//textarea[@id="additional_head_code"]');
        $this->assertSame(1, $textarea->length);
        $this->assertSame($code, $textarea->item(0)->textContent);

        $this->patch(route('admin.settings.update'), [
            'additional_head_code' => $textarea->item(0)->textContent,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $response = $this->get('/')->assertOk();
        $this->assertMatchesRegularExpression('/'.preg_quote($code, '/').'\\s*<\\/head>/', $response->getContent());
        $this->assertSame(1, substr_count($response->getContent(), $code));
    }

    public function test_non_admin_cannot_change_additional_head_code(): void
    {
        $this->actingAs(User::factory()->create())->patch(route('admin.settings.update'), [
            'additional_head_code' => '<script>window.extraCounter = true;</script>',
        ])->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => 'additional_head_code']);
    }

    public function test_additional_head_code_has_a_size_limit(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.settings.update'), [
                'additional_head_code' => str_repeat('a', 65001),
            ])->assertSessionHasErrors('additional_head_code');

        $this->assertDatabaseMissing('settings', ['key' => 'additional_head_code']);
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
