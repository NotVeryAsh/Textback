<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Lead;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'lead_id' => Lead::factory(),
            'direction' => 'out',
            'from' => '+15551112222',
            'to' => '+15553334444',
            'body' => fake()->sentence(),
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn () => [
            'direction' => 'in',
            'status' => 'received',
        ]);
    }
}
