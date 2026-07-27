<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Sequences\SequenceRunner;
use Illuminate\Console\Command;

class RunSequences extends Command
{
    protected $signature = 'textback:run-sequences';

    protected $description = 'Send any due pillar-3 sequence steps (invoice reminders / nurture) and advance enrollments';

    public function handle(SequenceRunner $runner): int
    {
        $sent = $runner->runDue();

        $this->info("Processed due sequence steps. Sent: {$sent}.");

        return self::SUCCESS;
    }
}
