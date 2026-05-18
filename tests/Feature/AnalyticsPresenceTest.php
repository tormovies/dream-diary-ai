<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsPresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_include_deferred_metrika_bootstrap(): void
    {
        foreach (
            [
                '/',
                route('legal.cookies', [], false),
                route('login', [], false),
            ] as $uri
        ) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('window.__COMPLIANCE__', false)
                ->assertSee('name="deferred-metrika-id"', false)
                ->assertDontSee('mc.yandex.ru/metrika/tag.js', false);
        }
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

    public function test_authenticated_dashboard_uses_deferred_metrika(): void
    {
        $user = User::factory()->create([
            'nickname' => 'testuser',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('window.__COMPLIANCE__', false)
            ->assertSee('name="deferred-metrika-id"', false);
    }

    public function test_dream_interpretations_list_uses_deferred_metrika(): void
    {
        $user = User::factory()->create([
            'nickname' => 'dreamer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dream-interpretations.index'))
            ->assertOk()
            ->assertSee('window.__COMPLIANCE__', false)
            ->assertSee('name="deferred-metrika-id"', false);
    }
}
