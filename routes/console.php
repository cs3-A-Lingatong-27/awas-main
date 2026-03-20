<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-weekly-teacher-summaries')
    ->weeklyOn(1, '04:00')
    ->timezone(config('app.timezone'));

Schedule::command('app:send-assessment-confirmation-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'));

Schedule::command('app:close-assessment-confirmations')
    ->dailyAt('17:00')
    ->timezone(config('app.timezone'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
