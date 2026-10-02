<?php

namespace App\Services;

use App\Models\ShowtimeSeat;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeatLockingService
{
    const LOCK_MINUTES = 10;

    // Lock seats
    public function lockSeats(
        int $showtimeId,
        array $seatLayoutIds,
        int $userId
    ): array {
        $locked  = [];
        $failed  = [];

        DB::beginTransaction();

        try {
            foreach ($seatLayoutIds as $seatLayoutId) {
                // SELECT FOR UPDATE — untuk cegah race condition
                $seat = ShowtimeSeat::where('showtime_id',   $showtimeId)
                    ->where('seat_layout_id', $seatLayoutId)
                    ->lockForUpdate()
                    ->first();

                if (!$seat) {
                    $failed[] = $seatLayoutId;
                    continue;
                }

                // Cek apakah kursi bisa di-lock
                $effectiveStatus = $this->getEffectiveStatus($seat);

                if ($effectiveStatus !== 'available') {
                    $failed[] = $seatLayoutId;
                    continue;
                }

                // Lock kursi
                $seat->update([
                    'status'            => 'locked',
                    'locked_by_user_id' => $userId,
                    'locked_until'      => now()->addMinutes(self::LOCK_MINUTES),
                ]);

                $locked[] = $seat->load('seatLayout');
            }

            DB::commit();

            Log::info('Seats locked', [
                'showtime_id' => $showtimeId,
                'user_id'     => $userId,
                'locked'      => count($locked),
                'failed'      => count($failed),
            ]);

            return [
                'success' => empty($failed),
                'locked'  => $locked,
                'failed'  => $failed,
                'message' => empty($failed)
                    ? 'Semua kursi berhasil dikunci.'
                    : count($failed) . ' kursi tidak tersedia.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Seat locking failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Release seats milik user tertentu
    public function releaseUserSeats(int $showtimeId, int $userId): int
    {
        $seats = ShowtimeSeat::where('showtime_id',        $showtimeId)
            ->where('status',              'locked')
            ->where('locked_by_user_id',   $userId)
            ->get();

        $count = 0;
        foreach ($seats as $seat) {
            $seat->update([
                'status'            => 'available',
                'locked_by_user_id' => null,
                'locked_until'      => null,
            ]);
            $count++;
        }

        return $count;
    }

    // Release satu seat spesifik
    public function releaseSeat(
        int $showtimeId,
        int $seatLayoutId,
        int $userId
    ): bool {
        $seat = ShowtimeSeat::where('showtime_id',        $showtimeId)
            ->where('seat_layout_id',      $seatLayoutId)
            ->where('locked_by_user_id',   $userId)
            ->where('status',              'locked')
            ->first();

        if (!$seat) return false;

        return (bool) $seat->update([
            'status'            => 'available',
            'locked_by_user_id' => null,
            'locked_until'      => null,
        ]);
    }

    // Auto-release semua expired locks
    public function releaseExpiredLocks(): int
    {
        $expired = ShowtimeSeat::where('status', 'locked')
            ->where('locked_until', '<=', now())
            ->get();

        $count = 0;
        foreach ($expired as $seat) {
            $seat->update([
                'status'            => 'available',
                'locked_by_user_id' => null,
                'locked_until'      => null,
            ]);
            $count++;
        }

        if ($count > 0) {
            Log::info("Released {$count} expired seat lock(s).");
        }

        return $count;
    }

    // Get seat map untuk suatu showtime
    public function getSeatMap(int $showtimeId, int $userId = 0): array
    {
        $seats = ShowtimeSeat::where('showtime_id', $showtimeId)
            ->with('seatLayout')
            ->get();

        $seatMap = [];

        foreach ($seats as $seat) {
            $layout          = $seat->seatLayout;
            $effectiveStatus = $this->getEffectiveStatus($seat);

            // Jika locked oleh user ini sendiri → tampilkan sebagai 'selected'
            $displayStatus = $effectiveStatus;
            if (
                $seat->status === 'locked'
                && $seat->locked_by_user_id === $userId
                && $seat->locked_until?->gt(now())
            ) {
                $displayStatus = 'selected';
            }

            $seatMap[$layout->row_label][] = [
                'id'              => $seat->id,
                'seat_layout_id'  => $layout->id,
                'seat_number'     => $layout->seat_number,
                'row_label'       => $layout->row_label,
                'col_number'      => $layout->col_number,
                'seat_type'       => $layout->seat_type,
                'status'          => $displayStatus,
                'locked_until'    => $seat->locked_until?->toISOString(),
                'remaining_secs'  => $seat->remaining_lock_seconds,
                'is_mine'         => $displayStatus === 'selected',
            ];
        }

        // Urutkan per baris & kolom
        foreach ($seatMap as $row => &$cols) {
            usort($cols, fn($a, $b) => $a['col_number'] <=> $b['col_number']);
        }

        ksort($seatMap);

        return $seatMap;
    }

    // Perpanjang lock milik user
    public function extendLocks(int $showtimeId, int $userId): bool
    {
        $updated = ShowtimeSeat::where('showtime_id',      $showtimeId)
            ->where('locked_by_user_id', $userId)
            ->where('status',            'locked')
            ->where('locked_until',      '>', now())
            ->update([
                'locked_until' => now()->addMinutes(self::LOCK_MINUTES),
            ]);

        return $updated > 0;
    }

    // Validasi lock sebelum checkout
    public function validateLockedSeats(
        int $showtimeId,
        array $seatLayoutIds,
        int $userId
    ): array {
        $valid   = [];
        $invalid = [];

        foreach ($seatLayoutIds as $seatLayoutId) {
            $seat = ShowtimeSeat::where('showtime_id',      $showtimeId)
                ->where('seat_layout_id', $seatLayoutId)
                ->first();

            if (
                $seat
                && $seat->status === 'locked'
                && $seat->locked_by_user_id === $userId
                && $seat->locked_until?->gt(now())
            ) {
                $valid[] = $seat;
            } else {
                $invalid[] = $seatLayoutId;
            }
        }

        return [
            'valid'   => $valid,
            'invalid' => $invalid,
            'is_valid' => empty($invalid),
        ];
    }

    // Helper: get status efektif (handle expired lock)
    private function getEffectiveStatus(ShowtimeSeat $seat): string
    {
        if (
            $seat->status === 'locked'
            && $seat->locked_until?->lt(now())
        ) {
            return 'available';
        }
        return $seat->status;
    }
}
