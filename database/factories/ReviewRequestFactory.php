<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\ReviewRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewRequest>
 */
class ReviewRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'client_name' => fake()->name(),
            'phone' => '+1555'.fake()->numerify('#######'),
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
