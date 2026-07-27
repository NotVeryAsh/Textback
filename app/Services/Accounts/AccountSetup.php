<?php

declare(strict_types=1);

namespace App\Services\Accounts;

use App\Enums\Vertical;
use App\Models\Account;
use App\Models\User;

class AccountSetup
{
    /**
     * Create the account for a user and seed its vertical template pack.
     */
    public function create(User $user, string $businessName, Vertical $vertical): Account
    {
        $account = $user->account()->create([
            'business_name' => $businessName,
            'vertical' => $vertical->value,
            'onboarding_step' => 'number',
        ]);

        // Start the "free until it works" trial clock (generic Cashier trial).
        if ($user->trial_ends_at === null) {
            $user->forceFill([
                'trial_ends_at' => now()->addDays((int) config('textback.trial_days')),
            ])->save();
        }

        $this->seedTemplates($account);
        $this->seedSequences($account);

        return $account;
    }

    /**
     * Copy the vertical's default templates onto the account. Existing
     * templates for a kind are left untouched (so edits survive re-seeding).
     */
    public function seedTemplates(Account $account): void
    {
        foreach ($account->vertical->defaultTemplates() as $kind => $body) {
            $account->templates()->firstOrCreate(
                ['kind' => $kind],
                ['body' => $body, 'is_active' => true],
            );
        }
    }

    /**
     * Seed the vertical's default pillar-3 sequence (and its steps) once.
     */
    public function seedSequences(Account $account): void
    {
        $definition = $account->vertical->defaultSequence();

        if ($definition === null) {
            return;
        }

        $sequence = $account->sequences()->firstOrCreate(
            ['kind' => $definition['kind']],
            [
                'name' => $definition['name'],
                'trigger' => $definition['trigger'],
                'is_active' => true,
            ],
        );

        if ($sequence->steps()->exists()) {
            return;
        }

        foreach ($definition['steps'] as $position => $step) {
            $sequence->steps()->create([
                'position' => $position,
                'delay_minutes' => $step['delay_minutes'],
                'body' => $step['body'],
                'channel' => 'sms',
                'is_active' => true,
            ]);
        }
    }
}
