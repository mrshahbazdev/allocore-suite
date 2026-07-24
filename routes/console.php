<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:check-alerts')->everyFiveMinutes();
Schedule::command('blog:publish-scheduled')->everyMinute();
Schedule::command('app:run-scheduled-reports')->hourly();
