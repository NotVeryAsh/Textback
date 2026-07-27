<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Jobs\SendSms;
use App\Models\Account;
use App\Services\Leads\MissedCallHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MissedCallHandlerTest extends TestCase
{
    use RefreshDatabase;

    private function handler(): MissedCallHandler
    {
        return app(MissedCallHandler::class);
    }

    public function test_it_captures_a_lead_and_queues_a_text_back(): void
    {
        Queue::fake();
        $account = Account::factory()->create();

        $lead = $this->handler()->handle($account, '+14155551234');

        $this->assertNotNull($lead);
        $this->assertSame(LeadStatus::TextedBack, $lead->status);
        $this->assertSame('+14155551234', $lead->phone);
        $this->assertSame(1, $account->fresh()->leads_recovered_count);

        $this->assertDatabaseHas('messages', [
            'account_id' => $account->id,
            'to' => '+14155551234',
            'direction' => 'out',
        ]);

        Queue::assertPushed(SendSms::class);
    }

    public function test_it_dedupes_repeat_calls_within_the_window(): void
    {
        Queue::fake();
        $account = Account::factory()->create();

        $this->handler()->handle($account, '+14155551234');
        $this->handler()->handle($account, '+14155551234');

        // Only one lead, counted once.
        $this->assertSame(1, $account->leads()->count());
        $this->assertSame(1, $account->fresh()->leads_recovered_count);
        Queue::assertPushed(SendSms::class, 1);
    }

    public function test_it_ignores_the_operators_own_number(): void
    {
        Queue::fake();
        $account = Account::factory()->create(['operator_cell' => '+14155550000']);

        $lead = $this->handler()->handle($account, '+14155550000');

        $this->assertNull($lead);
        Queue::assertNothingPushed();
    }

    public function test_it_returns_null_for_an_unusable_number(): void
    {
        Queue::fake();
        $account = Account::factory()->create();

        $this->assertNull($this->handler()->handle($account, 'not-a-number'));
    }
}
