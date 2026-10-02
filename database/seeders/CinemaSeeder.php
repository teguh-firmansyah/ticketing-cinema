<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\SeatLayout;
use App\Models\Studio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate dulu
        SeatLayout::query()->delete();
        Studio::query()->delete();
        Cinema::query()->delete();

        $cinemas = $this->getCinemaData();

        foreach ($cinemas as $cinemaData) {
            $studios = $cinemaData['studios'];
            unset($cinemaData['studios']);

            $cinema = Cinema::create(array_merge($cinemaData, [
                'slug' => Str::slug($cinemaData['name']),
            ]));

            foreach ($studios as $studioData) {
                $layout = $studioData['layout'];
                unset($studioData['layout']);

                $studio = Studio::create(array_merge($studioData, [
                    'cinema_id' => $cinema->id,
                ]));

                // Generate seat layout
                $this->generateSeatLayout($studio, $layout);

                // Sync total seats
                $studio->syncTotalSeats();
            }

            $this->command->line(
                "  → {$cinema->name} ({$cinema->city}) — "
                    . count($studios) . " studios"
            );
        }

        $this->command->info(
            '✓ Cinemas seeded: ' . count($cinemas)
        );
    }

    // Generate seat layout per studio
    private function generateSeatLayout(
        Studio $studio,
        array  $layoutConfig
    ): void {

        $rows         = $layoutConfig['rows'];
        $cols         = $layoutConfig['cols'];
        $vipRows      = $layoutConfig['vip_rows']    ?? [];
        $coupleRows   = $layoutConfig['couple_rows'] ?? [];
        $skipSeats    = $layoutConfig['skip_seats']  ?? [];
        $aisleAfter   = $layoutConfig['aisle_after'] ?? [];

        $batch = [];

        for ($r = 0; $r < $rows; $r++) {
            $rowLabel  = chr(65 + $r); // A, B, C, ...
            $colCount  = 0;

            for ($c = 1; $c <= $cols; $c++) {
                // Skip aisle (gang)
                if (in_array($c, $aisleAfter)) continue;

                // Skip seats yang diblokir
                $skipCols = $skipSeats[$rowLabel] ?? [];
                if (in_array($c, $skipCols)) {
                    // Tetap buat tapi tipe blocked
                    $batch[] = [
                        'studio_id'   => $studio->id,
                        'seat_number' => $rowLabel . $c,
                        'row_label'   => $rowLabel,
                        'col_number'  => $c,
                        'seat_type'   => 'blocked',
                        'is_active'   => false,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                    continue;
                }

                // Tentukan seat type
                $seatType = 'regular';

                if (in_array($rowLabel, $vipRows)) {
                    $seatType = 'vip';
                } elseif (in_array($rowLabel, $coupleRows)) {
                    $seatType = 'couple';
                }

                $batch[] = [
                    'studio_id'   => $studio->id,
                    'seat_number' => $rowLabel . $c,
                    'row_label'   => $rowLabel,
                    'col_number'  => $c,
                    'seat_type'   => $seatType,
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
                $colCount++;
            }
        }

        // Bulk insert untuk performa
        foreach (array_chunk($batch, 200) as $chunk) {
            SeatLayout::insert($chunk);
        }
    }

    private function getCinemaData(): array
    {
        return [

            // CGV Grand Indonesia
            [
                'name'        => 'CGV Grand Indonesia',
                'city'        => 'Jakarta',
                'address'     => 'Grand Indonesia Shopping Town, East Mall Lt. 8, Jl. M.H. Thamrin No.1, Jakarta Pusat',
                'phone'       => '021-23580999',
                'email'       => 'grandindo@cgv.id',
                'description' => 'Bioskop CGV terbesar di Jakarta Pusat dengan teknologi layar dan suara terkini. Terletak di pusat perbelanjaan Grand Indonesia.',
                'facilities'  => ['parking', 'food_court', 'atm', 'wifi', 'handicap_access', 'vip_lounge'],
                'latitude'    => -6.1966,
                'longitude'   => 106.8218,
                'maps_url'    => 'https://maps.google.com/?q=CGV+Grand+Indonesia',
                'is_active'   => true,
                'order'       => 1,
                'studios'     => [
                    [
                        'name'       => 'Studio 1',
                        'type'       => 'regular',
                        'rows'       => 10,
                        'cols'       => 20,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 1,
                        'layout'     => [
                            'rows'       => 10,
                            'cols'       => 20,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [4, 16],
                            'skip_seats' => [],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Studio 2',
                        'type'       => 'regular',
                        'rows'       => 9,
                        'cols'       => 18,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 2,
                        'layout'     => [
                            'rows'       => 9,
                            'cols'       => 18,
                            'vip_rows'   => ['A'],
                            'aisle_after' => [5, 14],
                            'skip_seats' => [],
                            'couple_rows' => ['I'],
                        ],
                    ],
                    [
                        'name'       => 'Studio 3 — 4DX',
                        'type'       => '4dx',
                        'rows'       => 8,
                        'cols'       => 14,
                        'facilities' => ['4dx_motion', 'water_effect', 'wind_effect', 'scent'],
                        'is_active'  => true,
                        'order'      => 3,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 14,
                            'vip_rows'   => [],
                            'aisle_after' => [7],
                            'skip_seats' => [
                                'A' => [1, 2, 13, 14],
                            ],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'IMAX',
                        'type'       => 'imax',
                        'rows'       => 12,
                        'cols'       => 22,
                        'facilities' => ['imax_laser', 'dolby_atmos', '4k_projection'],
                        'is_active'  => true,
                        'order'      => 4,
                        'layout'     => [
                            'rows'       => 12,
                            'cols'       => 22,
                            'vip_rows'   => ['A', 'B', 'C'],
                            'aisle_after' => [5, 18],
                            'skip_seats' => [
                                'A' => [1, 2, 21, 22],
                                'L' => [1, 2, 21, 22],
                            ],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Velvet (VIP)',
                        'type'       => 'vip',
                        'rows'       => 5,
                        'cols'       => 10,
                        'facilities' => ['recliner', 'private_lounge', 'food_service', 'blanket'],
                        'is_active'  => true,
                        'order'      => 5,
                        'layout'     => [
                            'rows'       => 5,
                            'cols'       => 10,
                            'vip_rows'   => ['A', 'B', 'C', 'D', 'E'],
                            'aisle_after' => [5],
                            'skip_seats' => [],
                            'couple_rows' => ['D', 'E'],
                        ],
                    ],
                ],
            ],

            // XXI Pondok Indah Mall
            [
                'name'        => 'XXI Pondok Indah Mall',
                'city'        => 'Jakarta',
                'address'     => 'Pondok Indah Mall 1 Lt. 3, Jl. Metro Pondok Indah, Jakarta Selatan',
                'phone'       => '021-7506250',
                'email'       => 'pim@21cineplex.com',
                'description' => 'Bioskop XXI premium di kawasan Pondok Indah dengan fasilitas lengkap dan studio berteknologi tinggi.',
                'facilities'  => ['parking', 'food_court', 'atm', 'wifi', 'handicap_access'],
                'latitude'    => -6.2637,
                'longitude'   => 106.7836,
                'maps_url'    => 'https://maps.google.com/?q=XXI+Pondok+Indah+Mall',
                'is_active'   => true,
                'order'       => 2,
                'studios'     => [
                    [
                        'name'       => 'Studio 1',
                        'type'       => 'regular',
                        'rows'       => 10,
                        'cols'       => 18,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 1,
                        'layout'     => [
                            'rows'       => 10,
                            'cols'       => 18,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [4, 14],
                            'skip_seats' => [],
                            'couple_rows' => ['J'],
                        ],
                    ],
                    [
                        'name'       => 'Studio 2',
                        'type'       => 'regular',
                        'rows'       => 9,
                        'cols'       => 16,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 2,
                        'layout'     => [
                            'rows'       => 9,
                            'cols'       => 16,
                            'vip_rows'   => ['A'],
                            'aisle_after' => [4, 13],
                            'skip_seats' => [],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Studio 3 — 3D',
                        'type'       => '3d',
                        'rows'       => 9,
                        'cols'       => 16,
                        'facilities' => ['3d_projection', 'dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 3,
                        'layout'     => [
                            'rows'       => 9,
                            'cols'       => 16,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [5, 12],
                            'skip_seats' => [],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Premier',
                        'type'       => 'premiere',
                        'rows'       => 6,
                        'cols'       => 12,
                        'facilities' => ['recliner', 'food_service', 'dolby_atmos'],
                        'is_active'  => true,
                        'order'      => 4,
                        'layout'     => [
                            'rows'       => 6,
                            'cols'       => 12,
                            'vip_rows'   => ['A', 'B', 'C', 'D', 'E', 'F'],
                            'aisle_after' => [6],
                            'skip_seats' => [],
                            'couple_rows' => ['E', 'F'],
                        ],
                    ],
                ],
            ],

            // Cinepolis Supermal Pakuwon
            [
                'name'        => 'Cinepolis Supermal Pakuwon',
                'city'        => 'Surabaya',
                'address'     => 'Supermal Pakuwon Lt. 4, Jl. Puncak Indah Lontar No.2, Surabaya',
                'phone'       => '031-7392888',
                'email'       => 'pakuwon@cinepolis.co.id',
                'description' => 'Bioskop modern di Surabaya Barat dengan studio berteknologi terkini dan pengalaman menonton premium.',
                'facilities'  => ['parking', 'food_court', 'atm', 'wifi', 'prayer_room', 'handicap_access'],
                'latitude'    => -7.2894,
                'longitude'   => 112.6659,
                'maps_url'    => 'https://maps.google.com/?q=Cinepolis+Supermal+Pakuwon+Surabaya',
                'is_active'   => true,
                'order'       => 3,
                'studios'     => [
                    [
                        'name'       => 'Studio 1',
                        'type'       => 'regular',
                        'rows'       => 9,
                        'cols'       => 18,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 1,
                        'layout'     => [
                            'rows'       => 9,
                            'cols'       => 18,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [5, 14],
                            'skip_seats' => [],
                            'couple_rows' => ['I'],
                        ],
                    ],
                    [
                        'name'       => 'Studio 2',
                        'type'       => 'regular',
                        'rows'       => 8,
                        'cols'       => 16,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 2,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 16,
                            'vip_rows'   => ['A'],
                            'aisle_after' => [4, 13],
                            'skip_seats' => [],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Studio 3 — 4DX',
                        'type'       => '4dx',
                        'rows'       => 7,
                        'cols'       => 12,
                        'facilities' => ['4dx_motion', 'water_effect', 'wind_effect'],
                        'is_active'  => true,
                        'order'      => 3,
                        'layout'     => [
                            'rows'       => 7,
                            'cols'       => 12,
                            'vip_rows'   => [],
                            'aisle_after' => [6],
                            'skip_seats' => [
                                'A' => [1, 2, 11, 12],
                            ],
                            'couple_rows' => [],
                        ],
                    ],
                ],
            ],

            // CGV Paris Van Java
            [
                'name'        => 'CGV Paris Van Java',
                'city'        => 'Bandung',
                'address'     => 'Paris Van Java Mall Lt. 4, Jl. Sukajadi No.137-139, Bandung',
                'phone'       => '022-82062999',
                'email'       => 'pvj@cgv.id',
                'description' => 'Bioskop CGV di jantung kota Bandung dengan pemandangan indah dan studio berteknologi tinggi.',
                'facilities'  => ['parking', 'food_court', 'atm', 'wifi', 'prayer_room'],
                'latitude'    => -6.8917,
                'longitude'   => 107.5950,
                'maps_url'    => 'https://maps.google.com/?q=CGV+Paris+Van+Java+Bandung',
                'is_active'   => true,
                'order'       => 4,
                'studios'     => [
                    [
                        'name'       => 'Studio 1',
                        'type'       => 'regular',
                        'rows'       => 9,
                        'cols'       => 16,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 1,
                        'layout'     => [
                            'rows'       => 9,
                            'cols'       => 16,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [4, 13],
                            'skip_seats' => [],
                            'couple_rows' => ['I'],
                        ],
                    ],
                    [
                        'name'       => 'Studio 2 — IMAX',
                        'type'       => 'imax',
                        'rows'       => 10,
                        'cols'       => 20,
                        'facilities' => ['imax_laser', 'dolby_atmos'],
                        'is_active'  => true,
                        'order'      => 2,
                        'layout'     => [
                            'rows'       => 10,
                            'cols'       => 20,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [5, 16],
                            'skip_seats' => [
                                'A'  => [1, 2, 19, 20],
                                'J'  => [1, 2, 19, 20],
                            ],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Studio 3',
                        'type'       => 'regular',
                        'rows'       => 8,
                        'cols'       => 14,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 3,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 14,
                            'vip_rows'   => ['A'],
                            'aisle_after' => [4, 11],
                            'skip_seats' => [],
                            'couple_rows' => ['H'],
                        ],
                    ],
                ],
            ],

            // Cinepolis Hartono Mall
            [
                'name'        => 'Cinepolis Hartono Mall',
                'city'        => 'Yogyakarta',
                'address'     => 'Hartono Mall Yogyakarta Lt. 3, Jl. Ring Road Utara, Sleman, Yogyakarta',
                'phone'       => '0274-2880888',
                'email'       => 'hartono@cinepolis.co.id',
                'description' => 'Bioskop modern di Yogyakarta dengan fasilitas terlengkap dan suasana yang nyaman untuk semua kalangan.',
                'facilities'  => ['parking', 'food_court', 'atm', 'wifi', 'prayer_room', 'nursing_room'],
                'latitude'    => -7.7325,
                'longitude'   => 110.3956,
                'maps_url'    => 'https://maps.google.com/?q=Cinepolis+Hartono+Mall+Yogyakarta',
                'is_active'   => true,
                'order'       => 5,
                'studios'     => [
                    [
                        'name'       => 'Studio 1',
                        'type'       => 'regular',
                        'rows'       => 8,
                        'cols'       => 16,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 1,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 16,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [4, 13],
                            'skip_seats' => [],
                            'couple_rows' => ['H'],
                        ],
                    ],
                    [
                        'name'       => 'Studio 2',
                        'type'       => 'regular',
                        'rows'       => 8,
                        'cols'       => 14,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 2,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 14,
                            'vip_rows'   => ['A'],
                            'aisle_after' => [4, 11],
                            'skip_seats' => [],
                            'couple_rows' => [],
                        ],
                    ],
                    [
                        'name'       => 'Studio 3 — 3D',
                        'type'       => '3d',
                        'rows'       => 7,
                        'cols'       => 14,
                        'facilities' => ['3d_projection'],
                        'is_active'  => true,
                        'order'      => 3,
                        'layout'     => [
                            'rows'       => 7,
                            'cols'       => 14,
                            'vip_rows'   => ['A'],
                            'aisle_after' => [4, 11],
                            'skip_seats' => [],
                            'couple_rows' => ['G'],
                        ],
                    ],
                ],
            ],

            // CGV Transmart Bali
            [
                'name'        => 'CGV Transmart Bali',
                'city'        => 'Bali',
                'address'     => 'Transmart Carrefour Bali Lt. 2, Jl. Sunset Road No.100, Kuta, Bali',
                'phone'       => '0361-8499999',
                'email'       => 'bali@cgv.id',
                'description' => 'Nikmati pengalaman menonton film terbaru di tengah destinasi wisata terfavorit dunia. CGV Bali menghadirkan hiburan premium di Kuta.',
                'facilities'  => ['parking', 'food_court', 'atm', 'wifi'],
                'latitude'    => -8.7101,
                'longitude'   => 115.1675,
                'maps_url'    => 'https://maps.google.com/?q=CGV+Transmart+Bali',
                'is_active'   => true,
                'order'       => 6,
                'studios'     => [
                    [
                        'name'       => 'Studio 1',
                        'type'       => 'regular',
                        'rows'       => 8,
                        'cols'       => 16,
                        'facilities' => ['dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 1,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 16,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [4, 13],
                            'skip_seats' => [],
                            'couple_rows' => ['H'],
                        ],
                    ],
                    [
                        'name'       => 'Studio 2 — 3D',
                        'type'       => '3d',
                        'rows'       => 8,
                        'cols'       => 14,
                        'facilities' => ['3d_projection', 'dolby_stereo'],
                        'is_active'  => true,
                        'order'      => 2,
                        'layout'     => [
                            'rows'       => 8,
                            'cols'       => 14,
                            'vip_rows'   => ['A', 'B'],
                            'aisle_after' => [4, 11],
                            'skip_seats' => [],
                            'couple_rows' => [],
                        ],
                    ],
                ],
            ],

        ];
    }
}
