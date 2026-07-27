<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_account_is_on_trial_and_not_subscribed(): void
    {
        $account = Account::factory()->create();
        $account->user->forceFill(['trial_ends_at' => now()->addDays(14)])->save();

        $billing = app(BillingService::class);

        $this->assertTrue($billing->onTrial($account));
        $this->assertFalse($billing->subscribed($account));
        $this->assertSame((int) config('textback.trial_lead_cap'), $billing->leadsRemainingInTrial($account));
        $this->assertFalse($billing->shouldPromptUpgrade($account));
    }

    public function test_trial_status_reads_from_a_fresh_database_record(): void
    {
        // Regression: trial_ends_at must be cast to a Carbon instance, or
        // Cashier's onGenericTrial() calls isFuture() on a string and 500s.
        $account = Account::factory()->create();
        $account->user->forceFill(['trial_ends_at' => now()->addDays(14)])->save();

        $fresh = Account::with('user')->find($account->id);

        $this->assertTrue(app(BillingService::class)->onTrial($fresh));
    }

    public function test_hitting_the_lead_cap_prompts_an_upgrade(): void
    {
        $account = Account::factory()->create([
            'leads_recovered_count' => (int) config('textback.trial_lead_cap'),
        ]);
        $account->user->forceFill(['trial_ends_at' => now()->addDays(14)])->save();

        $billing = app(BillingService::class);

        $this->assertTrue($account->leadCapReached());
        $this->assertTrue($billing->shouldPromptUpgrade($account));
        $this->assertSame(0, $billing->leadsRemainingInTrial($account));
    }
}
