<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Module B: automatic nightly room-night + board posting, per active tenant.
Schedule::command('hotel:nightly-posting')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onOneServer();
