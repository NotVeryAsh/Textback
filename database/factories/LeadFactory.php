<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'phone' => '+1555'.fake()->numerify('#######'),
            'name' => fake()->optional()->name(),
            'status' => 'texted_back',
            'source' => 'missed_call',
            'last_contacted_at' => now(),
        ];
    }
}
