<?php

namespace App\Jobs;

use App\Models\CinemaOrder;
use App\Services\CinemaOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireCinemaOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(CinemaOrderService $orderService): void
    {
        try {
            $expiredOrders = CinemaOrder::where('status', 'pending')
                ->where('expired_at', '<=', now())
                ->whereNotNull('expired_at')
                ->with(['orderItems.showtimeSeat', 'showtime'])
                ->get();

            $count = 0;

            foreach ($expiredOrders as $order) {
                $orderService->expireOrder($order);
                $count++;
            }

            if ($count > 0) {
                Log::info("ExpireCinemaOrdersJob: expired {$count} order(s).");
            }
        } catch (\Exception $e) {
            Log::error('ExpireCinemaOrdersJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
