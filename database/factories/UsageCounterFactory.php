<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\UsageCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsageCounter>
 */
class UsageCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'period' => now()->format('Y-m'),
            'sms_out' => 0,
            'sms_in' => 0,
            'call_minutes' => 0,
            'leads_recovered' => 0,
        ];
    }
}
