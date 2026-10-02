<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\CinemaOrder;
use App\Models\ShowtimeSeat;
use App\Models\Showtime;
use App\Services\CinemaOrderService;
use App\Services\SeatLockingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected CinemaOrderService $orderService,
        protected SeatLockingService $seatLocking,
    ) {}

    // Checkout page
    public function index(Showtime $showtime)
    {
        abort_if(!$showtime->isBookable(), 404);

        $showtime->load(['movie', 'studio.cinema']);

        // Ambil locked seats milik user ini
        $lockedSeats = ShowtimeSeat::where('showtime_id', $showtime->id)
            ->where('locked_by_user_id', auth()->id())
            ->where('status', 'locked')
            ->where('locked_until', '>', now())
            ->with('seatLayout')
            ->get();

        // Redirect ke seat map jika tidak ada locked seats
        if ($lockedSeats->isEmpty()) {
            return redirect()
                ->route('cinema.seat-map', $showtime)
                ->with('error', 'Pilih kursi terlebih dahulu.');
        }

        // Harga per seat type
        $prices = [
            'regular' => $showtime->price_regular,
            'student' => $showtime->price_student ?: $showtime->price_regular,
            'senior'  => $showtime->price_senior  ?: $showtime->price_regular,
            'vip'     => $showtime->price_vip      ?: $showtime->price_regular,
        ];

        // Expired at paling dekat
        $lockedUntil = $lockedSeats->min('locked_until');

        return view('cinema.checkout', compact(
            'showtime',
            'lockedSeats',
            'prices',
            'lockedUntil',
        ));
    }

    // Process checkout
    public function store(Request $request, Showtime $showtime)
    {
        $request->validate([
            'seats'                   => 'required|array|min:1',
            'seats.*.seat_layout_id'  => 'required|integer|exists:seat_layouts,id',
            'seats.*.ticket_type'     => 'required|in:regular,student,senior,vip',
        ]);

        try {
            $order = $this->orderService->createOrder(
                $showtime->id,
                auth()->id(),
                $request->seats,
            );

            return response()->json([
                'success'    => true,
                'order_id'   => $order->id,
                'snap_token' => $order->snap_token,
                'order_number' => $order->order_number,
                'total'      => $order->total,
            ]);
        } catch (\Exception $e) {
            Log::error('Cinema checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // Midtrans webhook notification
    public function notification(Request $request)
    {
        $serverKey   = config('services.midtrans.server_key');
        $orderId     = $request->order_id;
        $statusCode  = $request->status_code;
        $grossAmount = $request->gross_amount;

        // Verify signature
        $signature = hash(
            'sha512',
            $orderId . $statusCode . $grossAmount . $serverKey
        );

        if ($signature !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = CinemaOrder::where('order_number', $orderId)
            ->with(['orderItems.showtimeSeat', 'showtime'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $status = $request->transaction_status;

        if (in_array($status, ['capture', 'settlement'])) {
            $this->orderService->handlePaymentSuccess($order);
        } elseif (in_array($status, ['cancel', 'deny', 'expire'])) {
            $this->orderService->cancelOrder($order, "Pembayaran $status.");
        }

        return response()->json(['message' => 'OK']);
    }

    // Finish callback dari Midtrans
    public function finish(Request $request)
    {
        $orderId = $request->order_id;
        $order   = CinemaOrder::where('order_number', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return redirect()->route('cinema.index');
        }

        return redirect()->route('cinema.orders.show', $order->id)
            ->with('success', 'Pembayaran berhasil! Tiket sudah siap.');
    }
}
