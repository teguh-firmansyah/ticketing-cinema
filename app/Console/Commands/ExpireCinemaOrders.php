<?php

namespace App\Console\Commands;

use App\Jobs\ExpireCinemaOrdersJob;
use App\Jobs\ExpireCinemaTicketsJob;
use Illuminate\Console\Command;

class ExpireCinemaOrders extends Command
{
    protected $signature   = 'cinema:expire-orders';
    protected $description = 'Expire overdue cinema orders and old tickets';

    public function handle(): void
    {
        $this->info('Expiring cinema orders...');
        ExpireCinemaOrdersJob::dispatchSync();

        $this->info('Expiring cinema tickets...');
        ExpireCinemaTicketsJob::dispatchSync();

        $this->info('Done!');
    }
}
