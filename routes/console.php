<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('accounts:purge-deleted')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('credits:deduct-daily')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('set-and-forget:process')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('featured:expire')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('online-sessions:rollover')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();
