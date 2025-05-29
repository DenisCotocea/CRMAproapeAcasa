<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('import:olx-properties')->dailyAt('01:00');
Schedule::command('import:public-properties')->dailyAt('02:00');
Schedule::command('import:storia-properties')->dailyAt('03:00');
Schedule::command('verify:properties')->dailyAt('05:00');
