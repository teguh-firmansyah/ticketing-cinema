<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\ShowtimeSeat;
use App\Models\Showtime;
use App\Models\Studio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShowtimeSeeder extends Seeder
{
    public function run(): void
    {
        ShowtimeSeat::query()->delete();
        Showtime::query()->delete();

        $nowShowingMovies = Movie::where('status', 'now_showing')->get();

        if ($nowShowingMovies->isEmpty()) {
            $this->command->warn('⚠ No now_showing movies. Run MovieSeeder first.');
            return;
        }

        $cinemas = Cinema::with(['studios.seatLayouts'])->where('is_active', true)->get();

        if ($cinemas->isEmpty()) {
            $this->command->warn('⚠ No cinemas. Run CinemaSeeder first.');
            return;
        }

        $totalShowtimes = 0;

        foreach ($cinemas as $cinema) {
            foreach ($cinema->studios as $studio) {
                if (!$studio->is_active) continue;

                // Generate jadwal untuk 3 hari ke depan
                for ($day = 0; $day <= 3; $day++) {
                    $date     = now()->addDays($day);
                    $showtimes = $this->generateShowtimesForStudio(
                        $studio,
                        $nowShowingMovies,
                        $date
                    );

                    foreach ($showtimes as $showtime) {
                        $this->createShowtimeWithSeats($studio, $showtime);
                        $totalShowtimes++;
                    }
                }
            }

            $this->command->line(
                "  → {$cinema->name} — showtimes created"
            );
        }

        $this->command->info("✓ Showtimes seeded: {$totalShowtimes}");
    }

    // Generate daftar showtime per studio per hari
    private function generateShowtimesForStudio(
        Studio   $studio,
        $movies,
        \Carbon\Carbon $date
    ): array {
        $showtimes  = [];
        $studioType = $studio->type;

        // Pilih film secara bergiliran per studio
        $movieCount = $movies->count();
        $studioIndex = $studio->id % $movieCount;
        $movie       = $movies[$studioIndex];

        // Tentukan harga berdasarkan tipe studio
        $prices = $this->getPricesForStudio($studio);

        // Tentukan format berdasarkan tipe studio
        $format = match ($studioType) {
            'imax'     => 'imax',
            '3d'       => '3d',
            '4dx'      => '4dx',
            'vip',
            'premiere' => '2d',
            default    => '2d',
        };

        // Jam tayang berdasarkan tipe studio
        $times = $this->getShowTimesForType($studioType, $date);

        foreach ($times as $timeSlot) {
            $startTime = $timeSlot;
            $endTime   = (clone $startTime)->addMinutes($movie->duration + 30); // +30 iklan

            // Jika startTime sudah lewat, skip
            if ($startTime->lt(now()->addMinutes(30))) continue;

            $showtimes[] = [
                'movie_id'        => $movie->id,
                'studio_id'       => $studio->id,
                'start_time'      => $startTime,
                'end_time'        => $endTime,
                'price_regular'   => $prices['regular'],
                'price_student'   => $prices['student'],
                'price_senior'    => $prices['senior'],
                'price_vip'       => $prices['vip'],
                'language'        => 'sub',
                'format'          => $format,
                'status'          => 'open',
                'available_seats' => 0, // dihitung setelah insert seats
                'booked_seats'    => 0,
            ];
        }

        // Studio regular bisa punya 2 film berbeda
        if (in_array($studioType, ['regular', '3d']) && $movieCount > 1) {
            $secondMovieIndex = ($studioIndex + 1) % $movieCount;
            $secondMovie      = $movies[$secondMovieIndex];
            $secondTimes      = $this->getSecondShowTimes($studioType, $date);

            foreach ($secondTimes as $timeSlot) {
                $startTime = $timeSlot;
                $endTime   = (clone $startTime)->addMinutes($secondMovie->duration + 30);

                if ($startTime->lt(now()->addMinutes(30))) continue;

                $showtimes[] = [
                    'movie_id'        => $secondMovie->id,
                    'studio_id'       => $studio->id,
                    'start_time'      => $startTime,
                    'end_time'        => $endTime,
                    'price_regular'   => $prices['regular'],
                    'price_student'   => $prices['student'],
                    'price_senior'    => $prices['senior'],
                    'price_vip'       => $prices['vip'],
                    'language'        => 'dub',
                    'format'          => $format,
                    'status'          => 'open',
                    'available_seats' => 0,
                    'booked_seats'    => 0,
                ];
            }
        }

        return $showtimes;
    }

    // Create showtime + generate semua seat records
    private function createShowtimeWithSeats(
        Studio $studio,
        array  $showtimeData
    ): void {
        DB::beginTransaction();

        try {
            $showtime = Showtime::create($showtimeData);

            // Generate showtime_seats dari seat_layouts studio
            $seatLayouts = $studio->seatLayouts;
            $seats       = [];

            foreach ($seatLayouts as $layout) {
                $seats[] = [
                    'showtime_id'       => $showtime->id,
                    'seat_layout_id'    => $layout->id,
                    'locked_by_user_id' => null,
                    'status'            => $layout->is_active ? 'available' : 'disabled',
                    'locked_until'      => null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            // Bulk insert showtime seats
            foreach (array_chunk($seats, 200) as $chunk) {
                ShowtimeSeat::insert($chunk);
            }

            // Update available_seats di showtime
            $availableCount = collect($seats)
                ->where('status', 'available')
                ->count();

            $showtime->update(['available_seats' => $availableCount]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error(
                'Failed to create showtime: ' . $e->getMessage()
            );
        }
    }

    // Harga tiket berdasarkan tipe studio
    private function getPricesForStudio(Studio $studio): array
    {
        return match ($studio->type) {
            'imax' => [
                'regular' => 85000,
                'student' => 75000,
                'senior'  => 70000,
                'vip'     => 0,
            ],
            '4dx' => [
                'regular' => 100000,
                'student' => 90000,
                'senior'  => 85000,
                'vip'     => 0,
            ],
            '3d' => [
                'regular' => 55000,
                'student' => 45000,
                'senior'  => 40000,
                'vip'     => 0,
            ],
            'vip', 'premiere' => [
                'regular' => 0,
                'student' => 0,
                'senior'  => 0,
                'vip'     => 150000,
            ],
            default => [
                'regular' => 45000,
                'student' => 35000,
                'senior'  => 30000,
                'vip'     => 75000,
            ],
        };
    }

    // Jam tayang utama per tipe studio
    private function getShowTimesForType(
        string         $studioType,
        \Carbon\Carbon $date
    ): array {
        $times = match ($studioType) {
            'imax' => [
                $date->copy()->setTime(10, 30),
                $date->copy()->setTime(14, 0),
                $date->copy()->setTime(17, 30),
                $date->copy()->setTime(21, 0),
            ],
            '4dx' => [
                $date->copy()->setTime(11, 0),
                $date->copy()->setTime(15, 0),
                $date->copy()->setTime(19, 0),
            ],
            '3d' => [
                $date->copy()->setTime(10, 0),
                $date->copy()->setTime(13, 30),
                $date->copy()->setTime(17, 0),
                $date->copy()->setTime(20, 30),
            ],
            'vip', 'premiere' => [
                $date->copy()->setTime(12, 0),
                $date->copy()->setTime(16, 0),
                $date->copy()->setTime(20, 0),
            ],
            default => [
                $date->copy()->setTime(9, 30),
                $date->copy()->setTime(12, 30),
                $date->copy()->setTime(16, 0),
                $date->copy()->setTime(19, 30),
            ],
        };

        return $times;
    }

    // Jam tayang kedua untuk studio regular
    private function getSecondShowTimes(
        string         $studioType,
        \Carbon\Carbon $date
    ): array {
        return match ($studioType) {
            '3d' => [
                $date->copy()->setTime(11, 30),
                $date->copy()->setTime(18, 30),
            ],
            default => [
                $date->copy()->setTime(11, 0),
                $date->copy()->setTime(14, 30),
                $date->copy()->setTime(22, 0),
            ],
        };
    }
}
