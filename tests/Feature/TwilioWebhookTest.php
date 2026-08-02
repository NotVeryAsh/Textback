<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Jobs\SendSms;
use App\Models\Account;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TwilioWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_voice_webhook_returns_dial_twiml(): void
    {
        $account = Account::factory()->create([
            'twilio_number' => '+15557654321',
            'operator_cell' => '+15550000001',
        ]);

        $response = $this->post('/webhooks/twilio/voice', [
            'To' => $account->twilio_number,
            'From' => '+14155551234',
            'CallSid' => 'CAtest',
        ]);

        $response->assertOk();
        $response->assertSee('<Dial', false);
        $response->assertSee('+15550000001', false);
    }

    public function test_after_dial_no_answer_texts_the_caller_back(): void
    {
        Queue::fake();
        $account = Account::factory()->create(['twilio_number' => '+15557654321']);

        $this->post('/webhooks/twilio/after-dial', [
            'To' => $account->twilio_number,
            'From' => '+14155559999',
            'DialCallStatus' => 'no-answer',
        ])->assertOk();

        $this->assertDatabaseHas('leads', [
            'account_id' => $account->id,
            'phone' => '+14155559999',
            'status' => LeadStatus::TextedBack->value,
        ]);
        Queue::assertPushed(SendSms::class);
    }

    public function test_after_dial_completed_does_not_create_a_lead(): void
    {
        Queue::fake();
        $account = Account::factory()->create(['twilio_number' => '+15557654321']);

        $this->post('/webhooks/twilio/after-dial', [
            'To' => $account->twilio_number,
            'From' => '+14155559999',
            'DialCallStatus' => 'completed',
        ])->assertOk();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_inbound_sms_marks_the_lead_replied(): void
    {
        Queue::fake();
        $account = Account::factory()->create(['twilio_number' => '+15557654321']);
        $lead = Lead::factory()->for($account)->create([
            'phone' => '+14155558888',
            'status' => LeadStatus::TextedBack,
        ]);

        $this->post('/webhooks/twilio/sms', [
            'To' => $account->twilio_number,
            'From' => $lead->phone,
            'Body' => 'Yes, call me',
            'MessageSid' => 'SMtest',
        ])->assertOk();

        $this->assertSame(LeadStatus::Replied, $lead->fresh()->status);
        $this->assertDatabaseHas('messages', [
            'lead_id' => $lead->id,
            'direction' => 'in',
        ]);
    }

    public function test_inbound_stop_marks_the_lead_ignored(): void
    {
        Queue::fake();
        $account = Account::factory()->create(['twilio_number' => '+15557654321']);
        $lead = Lead::factory()->for($account)->create([
            'phone' => '+14155558888',
            'status' => LeadStatus::TextedBack,
        ]);

        $this->post('/webhooks/twilio/sms', [
            'To' => $account->twilio_number,
            'From' => $lead->phone,
            'Body' => 'STOP',
            'MessageSid' => 'SMtest2',
        ])->assertOk();

        $this->assertSame(LeadStatus::Ignored, $lead->fresh()->status);
    }

    public function test_lead_reply_notifies_the_operator(): void
    {
        Queue::fake();
        $account = Account::factory()->create([
            'twilio_number' => '+15557654321',
            'operator_cell' => '+15550000001',
        ]);
        $lead = Lead::factory()->for($account)->create(['phone' => '+14155558888']);

        $this->post('/webhooks/twilio/sms', [
            'To' => $account->twilio_number,
            'From' => $lead->phone,
            'Body' => 'Yes, please call me',
            'MessageSid' => 'SMnotify',
        ])->assertOk();

        // Operator is alerted (they respond in the app, not by personal SMS).
        Queue::assertPushed(\App\Jobs\ForwardInboundSms::class);
    }
}
