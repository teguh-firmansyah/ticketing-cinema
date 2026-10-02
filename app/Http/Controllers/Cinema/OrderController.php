<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\CinemaOrder;
use App\Services\CinemaOrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected CinemaOrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $status = $request->get('status', '');
        $search = $request->get('search', '');

        $query = CinemaOrder::where('user_id', auth()->id())
            ->with([
                'showtime.movie',
                'showtime.studio.cinema',
                'orderItems',
                'tickets',
            ])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas(
                        'showtime.movie',
                        fn($m) =>
                        $m->where('title', 'like', "%{$search}%")
                    );
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        $stats = [
            'total'     => CinemaOrder::where('user_id', auth()->id())->count(),
            'pending'   => CinemaOrder::where('user_id', auth()->id())->where('status', 'pending')->count(),
            'paid'      => CinemaOrder::where('user_id', auth()->id())->where('status', 'paid')->count(),
            'cancelled' => CinemaOrder::where('user_id', auth()->id())->whereIn('status', ['cancelled', 'expired'])->count(),
            'spent'     => CinemaOrder::where('user_id', auth()->id())->where('status', 'paid')->sum('total'),
        ];

        return view('cinema.orders.index', compact('orders', 'stats', 'status', 'search'));
    }

    public function show(CinemaOrder $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $order->load([
            'showtime.movie',
            'showtime.studio.cinema',
            'orderItems.showtimeSeat.seatLayout',
            'tickets',
        ]);

        // Snap token jika masih pending
        $snapToken = null;
        if ($order->isPending() && !$order->isExpired) {
            try {
                $snapToken = $order->snap_token
                    ?? app(\App\Services\MidtransService::class)
                    ->createSnapToken($order);
            } catch (\Exception $e) {
                // silent
            }
        }

        return view('cinema.orders.show', compact('order', 'snapToken'));
    }

    public function cancel(Request $request, CinemaOrder $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $request->validate([
            'cancel_reason' => 'required|string|min:10|max:500',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancel_reason.min'      => 'Alasan minimal 10 karakter.',
        ]);

        try {
            $this->orderService->cancelOrder(
                $order,
                'Dibatalkan oleh user: ' . $request->cancel_reason
            );

            return redirect()->route('cinema.my-orders')
                ->with('success', 'Order berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
