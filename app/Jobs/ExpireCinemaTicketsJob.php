<?php

namespace App\Jobs;

use App\Services\CinemaTicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireCinemaTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function handle(CinemaTicketService $ticketService): void
    {
        try {
            $count = $ticketService->expireOldTickets();

            if ($count > 0) {
                Log::info("ExpireCinemaTicketsJob: expired {$count} ticket(s).");
            }
        } catch (\Exception $e) {
            Log::error('ExpireCinemaTicketsJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
