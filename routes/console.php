<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pillar 3: fire due sequence steps (invoice reminders / nurture drips).
Schedule::command('textback:run-sequences')->everyMinute()->withoutOverlapping();
