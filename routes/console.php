<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ExpireOrdersJob;
use App\Jobs\ExpireTicketsJob;
use App\Jobs\ReleaseSeatLocksJob;
use App\Jobs\ExpireCinemaOrdersJob;
use App\Jobs\ExpireCinemaTicketsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Event
// Melakukan pengecekan setiap 15 menit
Schedule::job(new ExpireOrdersJob)->everyFifteenMinutes()
    ->name('expire-orders')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('ExpireOrdersJob schedule failed!');
    });

// Melakukan pengecekan setiap 1 jam
Schedule::job(new ExpireTicketsJob)->hourly()
    ->name('expire-tickets')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('ExpireTicketsJob schedule failed!');
    });

// Cinema
// Release seat locks setiap 1 menit
Schedule::job(new ReleaseSeatLocksJob)->everyMinute()
    ->name('release-seat-locks')
    ->withoutOverlapping();

// Expire cinema orders setiap 5 menit
Schedule::job(new ExpireCinemaOrdersJob)->everyFiveMinutes()
    ->name('expire-cinema-orders')
    ->withoutOverlapping();

// Expire cinema tickets setiap jam
Schedule::job(new ExpireCinemaTicketsJob)->hourly()
    ->name('expire-cinema-tickets')
    ->withoutOverlapping();
