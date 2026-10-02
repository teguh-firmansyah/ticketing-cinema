<?php

namespace App\Console\Commands;

use App\Jobs\ReleaseSeatLocksJob;
use Illuminate\Console\Command;

class ReleaseSeatLocks extends Command
{
    protected $signature   = 'cinema:release-locks';
    protected $description = 'Release all expired seat locks';

    public function handle(): void
    {
        $this->info('Releasing expired seat locks...');
        ReleaseSeatLocksJob::dispatchSync();
        $this->info('Done!');
    }
}
