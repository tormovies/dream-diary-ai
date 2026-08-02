<?php

namespace Tests\Feature;

use App\Models\CookieConsentLog;
use App\Models\UserRegistrationConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_personal_data_page_is_available(): void
    {
        $this->get(route('legal.personal-data'))
            ->assertOk()
            ->assertSee('Политика обработки персональных данных', false)
            ->assertSee(config('compliance.operator.inn'), false)
            ->assertSee('DeepSeek', false)
            ->assertSee('трансграничную передачу', false);
    }

    public function test_legal_terms_page_is_available(): void
    {
        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('Пользовательское соглашение', false)
            ->assertSee(config('compliance.operator.ogrnip'), false);
    }

    public function test_legal_cookie_policy_page_is_available(): void
    {
        $this->get(route('legal.cookies'))
            ->assertOk()
            ->assertSee('Политика использования файлов cookie', false)
            ->assertSee(config('compliance.operator.ogrnip'), false)
            ->assertSee('informative', false);
    }

    public function test_consent_endpoint_stores_log_entry(): void
    {
        $clientId = (string) Str::uuid();

        $this->postJson(route('consent.store'), [
            'client_id' => $clientId,
            'policy_version' => config('compliance.policy_version'),
            'necessary' => true,
            'analytics' => true,
        ])->assertOk()
            ->assertJson(['ok' => true, 'deduplicated' => false]);

        $this->assertDatabaseHas('cookie_consent_logs', [
            'client_id' => $clientId,
            'policy_version' => config('compliance.policy_version'),
            'analytics' => true,
            'necessary' => true,
        ]);
    }

    public function test_consent_endpoint_deduplicates_identical_consecutive_choices(): void
    {
        $clientId = (string) Str::uuid();
        $payload = [
            'client_id' => $clientId,
            'policy_version' => config('compliance.policy_version'),
            'necessary' => true,
            'analytics' => false,
        ];

        $this->postJson(route('consent.store'), $payload)->assertJson(['deduplicated' => false]);
        $this->postJson(route('consent.store'), $payload)->assertJson(['deduplicated' => true]);

        $this->assertSame(1, CookieConsentLog::query()->where('client_id', $clientId)->count());
    }

    public function test_consent_endpoint_rejects_invalid_policy_version(): void
    {
        $this->postJson(route('consent.store'), [
            'client_id' => (string) Str::uuid(),
            'policy_version' => '0.0',
            'necessary' => true,
            'analytics' => false,
        ])->assertUnprocessable();
    }

    public function test_static_sitemap_includes_legal_urls(): void
    {
        $response = $this->get(route('sitemap.static'));

        $response->assertOk();
        $response->assertSee(route('legal.personal-data', [], false), false);
        $response->assertSee(route('legal.terms', [], false), false);
        $response->assertSee(route('legal.cookies', [], false), false);
    }

    public function test_registration_stores_consent_log(): void
    {
        $this->post('/register', [
            'name' => 'Maria Ivanova',
            'nickname' => 'mariadreams',
            'email' => 'consent@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_accepted' => '1',
            'personal_data_consent' => '1',
        ])->assertRedirect(route('notifications.index', absolute: false));

        $this->assertDatabaseHas('user_registration_consents', [
            'policy_version' => config('compliance.policy_version'),
        ]);

        $this->assertSame(1, UserRegistrationConsent::query()->count());
    }
}
