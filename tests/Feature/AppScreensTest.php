<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Vertical;
use App\Livewire\Billing\Billing;
use App\Livewire\Dashboard;
use App\Livewire\Leads\LeadsIndex;
use App\Livewire\Onboarding\Wizard;
use App\Livewire\Reviews\ReviewsIndex;
use App\Livewire\Settings\Settings;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_an_onboarded_account(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create(['business_name' => 'Testburg Realty']);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertOk()
            ->assertSee('Testburg Realty');
    }

    public function test_leads_screen_renders(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(LeadsIndex::class)
            ->assertOk()
            ->assertSee('Leads');
    }

    public function test_reviews_settings_and_billing_screens_render(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create();

        Livewire::actingAs($user)->test(ReviewsIndex::class)->assertOk()->assertSee('Review requests');
        Livewire::actingAs($user)->test(Settings::class)->assertOk()->assertSee('Settings');
        Livewire::actingAs($user)->test(Billing::class)->assertOk()->assertSee('Billing');
    }

    public function test_onboarding_creates_account_from_business_step(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Wizard::class)
            ->set('business_name', 'Fresh Start Co')
            ->set('vertical', 'realtor')
            ->call('saveBusiness')
            ->assertSet('step', 'number');

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'business_name' => 'Fresh Start Co',
            'vertical' => Vertical::Realtor->value,
        ]);
        // Vertical template pack seeded.
        $this->assertDatabaseHas('templates', [
            'account_id' => $user->fresh()->account->id,
            'kind' => 'missed_call',
        ]);
    }

    public function test_onboarded_middleware_redirects_when_not_set_up(): void
    {
        $user = User::factory()->create(); // no account yet

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('onboarding'));
    }

    public function test_dashboard_route_renders_full_page_with_layout(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create(['business_name' => 'Routed Realty']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Routed Realty')
            ->assertSee('Leads'); // nav link present
    }

    public function test_marketing_home_is_public(): void
    {
        $this->get('/')->assertOk()->assertSee('Never lose a lead');
    }

    public function test_billing_and_dashboard_routes_render_for_a_trialing_user(): void
    {
        // Regression for the trial_ends_at cast 500 on /billing and /dashboard.
        $user = User::factory()->create();
        Account::factory()->for($user)->create();
        $user->forceFill(['trial_ends_at' => now()->addDays(10)])->save();

        $this->actingAs($user)->get('/billing')->assertOk()->assertSee('Billing');
        $this->actingAs($user)->get('/dashboard')->assertOk();
    }
}
