<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run these every 2 hours
Schedule::command('scrape:olx')->everyTwoHours();
Schedule::command('scrape:publi24')->everyTwoHours();
Schedule::command('scrape:storia')->everyTwoHours();

// Run this specifically at 05:00
Schedule::command('verify:properties')->dailyAt('05:00');
