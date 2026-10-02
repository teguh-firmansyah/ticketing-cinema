<?php

namespace App\Services;

use App\Models\CinemaOrder;
use App\Models\CinemaOrderItem;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CinemaOrderService
{
    public function __construct(
        protected SeatLockingService  $seatLocking,
        protected CinemaTicketService $ticketService,
        protected MidtransService     $midtrans,
    ) {}

    // Buat order dari locked seats
    public function createOrder(
        int    $showtimeId,
        int    $userId,
        array  $seats,
    ): CinemaOrder {

        $showtime = Showtime::with('studio.cinema')->findOrFail($showtimeId);

        // Validasi showtime masih bookable
        if (!$showtime->isBookable()) {
            throw new \Exception('Jadwal tayang ini tidak tersedia untuk dipesan.');
        }

        DB::beginTransaction();

        try {
            // Validasi semua kursi masih terkunci oleh user
            $seatLayoutIds  = array_column($seats, 'seat_layout_id');
            $validation     = $this->seatLocking->validateLockedSeats(
                $showtimeId,
                $seatLayoutIds,
                $userId
            );

            if (!$validation['is_valid']) {
                throw new \Exception(
                    'Beberapa kursi tidak lagi tersedia: '
                        . implode(', ', $validation['invalid'])
                );
            }

            // Menghitung subtotal
            $subtotal = 0;
            $items    = [];

            foreach ($seats as $seatData) {
                $showtimeSeat = ShowtimeSeat::where('showtime_id',    $showtimeId)
                    ->where('seat_layout_id', $seatData['seat_layout_id'])
                    ->with('seatLayout')
                    ->firstOrFail();

                $ticketType = $seatData['ticket_type'] ?? 'regular';
                $price      = $showtime->getPriceFor($ticketType);

                // Override harga jika VIP seat
                if ($showtimeSeat->seatLayout->seat_type === 'vip') {
                    $price = max($price, (float) $showtime->price_vip);
                }

                $items[]   = [
                    'showtime_seat'  => $showtimeSeat,
                    'ticket_type'    => $ticketType,
                    'price'          => $price,
                    'seat_number'    => $showtimeSeat->seatLayout->seat_number,
                    'seat_type'      => $showtimeSeat->seatLayout->seat_type,
                ];
                $subtotal += $price;
            }

            $order = CinemaOrder::create([
                'user_id'      => $userId,
                'showtime_id'  => $showtimeId,
                'subtotal'     => $subtotal,
                'discount'     => 0,
                'total'        => $subtotal,
                'status'       => 'pending',
                'expired_at'   => now()->addMinutes(15),
            ]);

            // Buat CinemaOrderItem per kursi
            foreach ($items as $item) {
                CinemaOrderItem::create([
                    'cinema_order_id'  => $order->id,
                    'showtime_seat_id' => $item['showtime_seat']->id,
                    'ticket_type'      => $item['ticket_type'],
                    'price'            => $item['price'],
                    'seat_number'      => $item['seat_number'],
                    'seat_type'        => $item['seat_type'],
                ]);
            }

            // Snap token Midtrans
            $snapToken = $this->createSnapToken($order);
            $order->update(['snap_token' => $snapToken]);

            DB::commit();

            Log::info('Cinema order created', [
                'order_number' => $order->order_number,
                'user_id'      => $userId,
                'total'        => $order->total,
                'seats'        => count($seats),
            ]);

            return $order->fresh(['orderItems', 'showtime.movie', 'showtime.studio.cinema']);
        } catch (\Exception $e) {
            DB::rollBack();

            // Release locked seats jika order gagal dibuat
            $this->seatLocking->releaseUserSeats($showtimeId, $userId);

            Log::error('Cinema order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Handle sukses pembayaran
    public function handlePaymentSuccess(
        CinemaOrder $order,
        array       $paymentData = []
    ): CinemaOrder {
        DB::beginTransaction();

        try {
            // Update status order
            $order->update([
                'status'     => 'paid',
                'expired_at' => null,
            ]);

            // Book semua kursi
            foreach ($order->orderItems as $item) {
                $item->showtimeSeat->update([
                    'status'            => 'booked',
                    'locked_by_user_id' => null,
                    'locked_until'      => null,
                ]);
            }

            // Sync available seats di showtime
            $order->showtime->syncAvailableSeats();

            // Generate tiket + QR code
            $this->ticketService->generateTickets($order);

            DB::commit();

            Log::info('Cinema payment success', [
                'order_number' => $order->order_number,
                'total'        => $order->total,
            ]);

            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cinema payment success handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Cancel order
    public function cancelOrder(
        CinemaOrder $order,
        string      $reason = ''
    ): bool {
        if (!$order->isPending()) {
            throw new \Exception('Hanya order pending yang dapat dibatalkan.');
        }

        DB::beginTransaction();

        try {
            // Update status order
            $order->update([
                'status' => 'cancelled',
                'notes'  => $reason ?: 'Dibatalkan oleh pengguna',
            ]);

            // Release semua locked seats
            foreach ($order->orderItems as $item) {
                if ($item->showtimeSeat->status === 'locked') {
                    $item->showtimeSeat->update([
                        'status'            => 'available',
                        'locked_by_user_id' => null,
                        'locked_until'      => null,
                    ]);
                }
            }

            // Sync available seats
            $order->showtime->syncAvailableSeats();

            DB::commit();

            Log::info('Cinema order cancelled', [
                'order_number' => $order->order_number,
                'reason'       => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cinema order cancel failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Expire order yang melewati batas waktu
    public function expireOrder(CinemaOrder $order): bool
    {
        if (!$order->isPending()) return false;

        DB::beginTransaction();

        try {
            $order->update(['status' => 'expired']);

            // Release semua locked seats
            foreach ($order->orderItems()->with('showtimeSeat')->get() as $item) {
                if (in_array($item->showtimeSeat->status, ['locked'])) {
                    $item->showtimeSeat->update([
                        'status'            => 'available',
                        'locked_by_user_id' => null,
                        'locked_until'      => null,
                    ]);
                }
            }

            $order->showtime->syncAvailableSeats();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cinema order expire failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Buat Midtrans snap token untuk cinema order
    private function createSnapToken(CinemaOrder $order): string
    {
        $order->load([
            'user',
            'showtime.movie',
            'showtime.studio.cinema',
            'orderItems',
        ]);

        $showtime = $order->showtime;
        $movie    = $showtime->movie;
        $cinema   = $showtime->studio->cinema;

        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email'      => $order->user->email,
                'phone'      => $order->user->phone ?? '',
            ],
            'item_details' => $order->orderItems->map(function ($item) use ($movie, $cinema, $showtime) {
                return [
                    'id'       => 'seat-' . $item->id,
                    'price'    => (int) $item->price,
                    'quantity' => 1,
                    'name'     => sprintf(
                        '%s — Kursi %s (%s) [%s]',
                        $movie->title,
                        $item->seat_number,
                        ucfirst($item->ticket_type),
                        $showtime->start_time->format('d M H:i')
                    ),
                ];
            })->toArray(),
            'callbacks' => [
                'finish' => route('cinema.checkout.finish'),
            ],
        ];

        return \Midtrans\Snap::getSnapToken($params);
    }

    // Get order summary
    public function getOrderSummary(CinemaOrder $order): array
    {
        $order->load([
            'showtime.movie',
            'showtime.studio.cinema',
            'orderItems',
            'tickets',
        ]);

        $showtime = $order->showtime;

        return [
            'order_number'  => $order->order_number,
            'movie_title'   => $showtime->movie->title,
            'cinema_name'   => $showtime->studio->cinema->name,
            'studio_name'   => $showtime->studio->name,
            'show_time'     => $showtime->start_time->format('d M Y, H:i'),
            'format'        => $showtime->format_label,
            'language'      => $showtime->language_label,
            'seats'         => $order->orderItems->map(fn($i) => [
                'seat'        => $i->seat_number,
                'type'        => ucfirst($i->ticket_type),
                'seat_type'   => $i->seat_type,
                'price'       => $i->price,
            ])->toArray(),
            'total_tickets' => $order->orderItems->count(),
            'subtotal'      => $order->subtotal,
            'discount'      => $order->discount,
            'total'         => $order->total,
            'status'        => $order->status,
            'expired_at'    => $order->expired_at?->toISOString(),
            'snap_token'    => $order->snap_token,
        ];
    }
}
