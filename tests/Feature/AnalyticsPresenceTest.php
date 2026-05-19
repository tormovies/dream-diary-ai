<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsPresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load_metrika_immediately_when_banner_disabled(): void
    {
        config(['compliance.cookie_banner_enabled' => false]);

        foreach (['/', route('legal.cookies', [], false), route('login', [], false)] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('mc.yandex.ru/metrika/tag.js', false)
                ->assertDontSee('name="deferred-metrika-id"', false)
                ->assertDontSee('id="cookie-consent-banner"', false);
        }
    }

    public function test_public_pages_defer_metrika_when_banner_enabled(): void
    {
        config(['compliance.cookie_banner_enabled' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="deferred-metrika-id"', false)
            ->assertDontSee('mc.yandex.ru/metrika/tag.js', false)
            ->assertSee('id="cookie-consent-banner"', false);
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
