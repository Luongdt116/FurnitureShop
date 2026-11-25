<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(\App\Services\CancellationService::class)->cancelExpiredOnlinePayments())
    ->name('orders.cancel-expired-online-payments')
    ->everyMinute()
    ->withoutOverlapping();
