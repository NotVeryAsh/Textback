<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Onboarding\Wizard;
use App\Livewire\SetupChecklist;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingAndAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_slim_three_step_onboarding_completes(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Wizard::class)
            ->set('business_name', 'Quick Start Co')
            ->set('vertical', 'contractor')
            ->call('saveBusiness')
            ->assertSet('step', 'number')
            ->set('manual_number', '+14155550123') // Twilio not configured in tests -> manual path
            ->call('saveManualNumber')
            ->assertSet('step', 'activate')
            ->set('operator_cell', '+14155559999')
            ->call('finishSetup')
            ->assertRedirect(route('dashboard'));

        $account = $user->fresh()->account;
        $this->assertTrue($account->isOnboarded());
        $this->assertTrue($account->is_live);
        $this->assertSame('+14155559999', $account->operator_cell);
    }

    public function test_google_callback_creates_and_logs_in_user(): void
    {
        config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret']);

        $abstract = (new SocialiteUser)->map([
            'id' => 'g-123',
            'name' => 'Dana Doe',
            'email' => 'dana@example.com',
        ]);

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($abstract);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'dana@example.com', 'name' => 'Dana Doe']);
        $this->assertAuthenticated();
    }

    public function test_google_routes_404_when_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get('/auth/google/redirect')->assertNotFound();
    }

    public function test_setup_checklist_phone_verify_and_dismiss(): void
    {
        config(['textback.require_real_otp' => false]);

        $user = User::factory()->create();
        Account::factory()->for($user)->create([
            'operator_cell' => '+14155550123',
            'operator_cell_verified_at' => null,
        ]);

        Livewire::actingAs($user)
            ->test(SetupChecklist::class)
            ->assertSee('Finish setting up')
            ->call('sendCode')
            ->assertSet('verifying', true)
            ->set('cell_code', '123456')
            ->call('verify');

        $this->assertNotNull($user->fresh()->account->operator_cell_verified_at);

        Livewire::actingAs($user)->test(SetupChecklist::class)->call('dismiss');
        $this->assertTrue($user->fresh()->account->setup_dismissed);
    }
}
