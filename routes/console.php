<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Schedule::command('app:send-birthday-wishes')
//    ->dailyAt('07:00')   // morning 9 baje
//    ->timezone('Asia/Kolkata')
//    ->withoutOverlapping();



Schedule::command('app:send-birthday-wishes')
    ->everyMinute()
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping();

Schedule::call(function () {
    Log::info('SCHEDULER HIT at '.now());
})->everyMinute();
