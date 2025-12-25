<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:weekly')
    ->sundays()
    ->at('23:00')
    ->timezone('Asia/Hebron')
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();
