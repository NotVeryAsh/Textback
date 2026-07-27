<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\Leads\MissedCallHandler;
use Illuminate\Console\Command;

class SimulateMissedCall extends Command
{
    protected $signature = 'textback:simulate-missed-call
        {account? : Account ID (defaults to the first account)}
        {--from= : Caller phone number (defaults to a random US number)}';

    protected $description = 'Simulate a missed call so you can test text-back end to end without Twilio';

    public function handle(MissedCallHandler $handler): int
    {
        $account = $this->argument('account')
            ? Account::find($this->argument('account'))
            : Account::first();

        if ($account === null) {
            $this->error('No account found. Register and complete onboarding first, or run the seeder.');

            return self::FAILURE;
        }

        $from = $this->option('from') ?: '+1555'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);

        $this->info("Simulating a missed call to {$account->business_name} from {$from} ...");

        $lead = $handler->handle($account, $from);

        if ($lead === null) {
            $this->warn('No lead created (number unusable, deduped, or caller is the operator).');

            return self::SUCCESS;
        }

        $this->info("Lead #{$lead->id} captured. Status: {$lead->status->value}.");
        $this->line('An outbound text-back message was queued. Process it with: php artisan queue:work --once');
        $this->line("Account leads recovered so far: {$account->fresh()->leads_recovered_count}");

        return self::SUCCESS;
    }
}
