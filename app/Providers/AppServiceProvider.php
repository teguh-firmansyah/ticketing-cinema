<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Event;
use App\Observers\OrderObserver;
use App\Observers\EventObserver;
use Illuminate\Support\ServiceProvider;
use App\View\Components\Mail\Layout as MailLayout;
use Illuminate\Support\Facades\Blade;
use App\Services\CinemaOrderService;
use App\Services\CinemaTicketService;
use App\Services\MidtransService;
use App\Services\SeatLockingService;
use App\View\Components\CinemaLayout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services sebagai singleton
        $this->app->singleton(SeatLockingService::class);
        $this->app->singleton(CinemaTicketService::class);

        $this->app->singleton(CinemaOrderService::class, function ($app) {
            return new CinemaOrderService(
                $app->make(SeatLockingService::class),
                $app->make(CinemaTicketService::class),
                $app->make(MidtransService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Event::observe(EventObserver::class);

        // Register mail layout component
        Blade::component('mail::layout', MailLayout::class);

        Blade::component('cinema-layout', CinemaLayout::class);
    }
}
