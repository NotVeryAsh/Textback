<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhoneNumber>
 */
class PhoneNumberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'e164' => '+1555'.fake()->numerify('#######'),
            'twilio_sid' => 'PN'.fake()->uuid(),
            'capabilities' => ['voice' => true, 'sms' => true],
            'status' => 'active',
        ];
    }
}
