<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Services\SeatLockingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatMapController extends Controller
{
    public function __construct(
        protected SeatLockingService $seatLocking
    ) {}

    // Tampilkan seat map
    public function index(Showtime $showtime)
    {
        abort_if(!$showtime->isBookable(), 404, 'Jadwal tidak tersedia.');

        $showtime->load([
            'movie',
            'studio.cinema',
            'studio.seatLayouts',
        ]);

        // Seat map dengan status per user
        $seatMap = $this->seatLocking->getSeatMap(
            $showtime->id,
            auth()->id()
        );

        // Hitung seat stats
        $seatStats = [
            'total'     => $showtime->studio->total_seats,
            'available' => $showtime->available_seats,
            'booked'    => $showtime->booked_seats,
            'selected'  => collect($seatMap)
                ->flatten(1)
                ->where('status', 'selected')
                ->count(),
        ];

        // Harga per ticket type
        $prices = [
            'regular' => $showtime->price_regular,
            'student' => $showtime->price_student ?: $showtime->price_regular,
            'senior'  => $showtime->price_senior  ?: $showtime->price_regular,
            'vip'     => $showtime->price_vip      ?: $showtime->price_regular,
        ];

        return view('cinema.seat-map', compact(
            'showtime',
            'seatMap',
            'seatStats',
            'prices',
        ));
    }

    // Lock seat (AJAX)
    public function lock(Request $request, Showtime $showtime)
    {
        $request->validate([
            'seat_layout_id' => 'required|integer|exists:seat_layouts,id',
        ]);

        try {
            $result = $this->seatLocking->lockSeats(
                $showtime->id,
                [$request->seat_layout_id],
                auth()->id()
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kursi tidak tersedia.',
                ], 422);
            }

            $seat = $result['locked'][0];

            return response()->json([
                'success'         => true,
                'seat_layout_id'  => $seat->seat_layout_id,
                'seat_number'     => $seat->seatLayout->seat_number,
                'seat_type'       => $seat->seatLayout->seat_type,
                'locked_until'    => $seat->locked_until->toISOString(),
                'remaining_secs'  => SeatLockingService::LOCK_MINUTES * 60,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Release seat (AJAX)
    public function release(Request $request, Showtime $showtime)
    {
        $request->validate([
            'seat_layout_id' => 'required|integer|exists:seat_layouts,id',
        ]);

        $released = $this->seatLocking->releaseSeat(
            $showtime->id,
            $request->seat_layout_id,
            auth()->id()
        );

        return response()->json([
            'success' => $released,
            'message' => $released ? 'Kursi berhasil dilepas.' : 'Gagal melepas kursi.',
        ]);
    }

    // Status semua seat
    public function status(Showtime $showtime)
    {
        $seatMap = $this->seatLocking->getSeatMap(
            $showtime->id,
            auth()->id()
        );

        // Flatten untuk response efisien
        $seats = collect($seatMap)->flatten(1)->map(fn($s) => [
            'seat_layout_id' => $s['seat_layout_id'],
            'status'         => $s['status'],
            'remaining_secs' => $s['remaining_secs'] ?? 0,
        ]);

        return response()->json([
            'seats'          => $seats,
            'available_seats' => $showtime->fresh()->available_seats,
        ]);
    }

    // Extend lock (refresh timer)
    public function extend(Showtime $showtime)
    {
        $extended = $this->seatLocking->extendLocks(
            $showtime->id,
            auth()->id()
        );

        return response()->json([
            'success'    => $extended,
            'locked_until' => now()->addMinutes(SeatLockingService::LOCK_MINUTES)
                ->toISOString(),
        ]);
    }
}
