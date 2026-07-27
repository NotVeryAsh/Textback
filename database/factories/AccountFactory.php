<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'vertical' => 'realtor',
            'operator_cell' => '+1555'.fake()->numerify('#######'),
            'operator_cell_verified_at' => now(),
            'twilio_number' => '+1555'.fake()->numerify('#######'),
            'twilio_number_sid' => 'PN'.fake()->uuid(),
            'google_review_link' => 'https://g.page/r/'.fake()->lexify('??????????'),
            'caller_id_mode' => 'lead',
            'onboarding_step' => 'done',
            'is_live' => true,
        ];
    }

    public function onboarding(): static
    {
        return $this->state(fn () => [
            'onboarding_step' => 'business',
            'is_live' => false,
            'operator_cell_verified_at' => null,
        ]);
    }
}
