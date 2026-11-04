<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('access-logs:prune')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('visitation:expire')
    ->everyFiveMinutes();
    
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
