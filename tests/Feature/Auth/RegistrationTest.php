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
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '(425) 472-1713',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sms_consent' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertSame('+14254721713', auth()->user()->phone);
    }

    public function test_registration_requires_a_valid_phone(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => 'not-a-number',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sms_consent' => '1',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }
}
