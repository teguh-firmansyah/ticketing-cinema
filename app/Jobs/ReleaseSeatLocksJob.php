<?php

namespace App\Jobs;

use App\Services\SeatLockingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReleaseSeatLocksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function handle(SeatLockingService $seatLocking): void
    {
        try {
            $count = $seatLocking->releaseExpiredLocks();

            Log::info("ReleaseSeatLocksJob: released {$count} expired lock(s).");
        } catch (\Exception $e) {
            Log::error('ReleaseSeatLocksJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
