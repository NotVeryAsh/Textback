<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Services\Twilio\TwilioClientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerTenantTwilioTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_without_own_creds_uses_global_config(): void
    {
        $account = Account::factory()->create();

        $this->assertFalse($account->usesOwnTwilio());

        config(['services.twilio.messaging_service_sid' => 'MGglobal']);
        $this->assertSame('MGglobal', $account->messagingServiceSid());
    }

    public function test_account_with_own_creds_is_detected_and_token_encrypted(): void
    {
        $account = Account::factory()->create([
            'twilio_account_sid' => 'ACclient123',
            'twilio_auth_token' => 'secret-token',
            'twilio_messaging_service_sid' => 'MGclient456',
        ]);

        $this->assertTrue($account->usesOwnTwilio());
        $this->assertSame('MGclient456', $account->messagingServiceSid());

        // Token must be encrypted at rest, not plaintext.
        $raw = $account->getRawOriginal('twilio_auth_token');
        $this->assertNotSame('secret-token', $raw);
        $this->assertSame('secret-token', $account->twilio_auth_token);
    }

    public function test_factory_configured_counts_account_creds_without_global(): void
    {
        config(['services.twilio.sid' => null, 'services.twilio.token' => null]);

        $factory = new TwilioClientFactory;
        $this->assertFalse($factory->configured());

        $account = Account::factory()->create([
            'twilio_account_sid' => 'ACclient123',
            'twilio_auth_token' => 'secret-token',
        ]);

        $this->assertTrue($factory->configured($account));
    }

    public function test_factory_builds_client_with_account_credentials(): void
    {
        $account = Account::factory()->create([
            'twilio_account_sid' => 'ACclient123',
            'twilio_auth_token' => 'secret-token',
        ]);

        $client = (new TwilioClientFactory)->for($account);

        $this->assertSame('ACclient123', $client->getAccountSid());
    }
}
