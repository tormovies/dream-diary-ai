<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alex Smith',
            'nickname' => 'dreamer42',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_accepted' => '1',
            'personal_data_consent' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
        $response->assertRedirect(route('notifications.index', absolute: false));
    }

    public function test_registration_requires_personal_data_consent(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alex Smith',
            'nickname' => 'dreamer99',
            'email' => 'test2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => '1',
        ]);

        $response->assertSessionHasErrors('personal_data_consent');
        $this->assertGuest();
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alex Smith',
            'nickname' => 'dreamer88',
            'email' => 'test3@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'personal_data_consent' => '1',
        ]);

        $response->assertSessionHasErrors('terms_accepted');
        $this->assertGuest();
    }
}
