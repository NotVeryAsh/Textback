<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'kind' => 'missed_call',
            'body' => 'Hi, sorry I missed your call! How can I help?',
            'is_active' => true,
        ];
    }
}
