<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\ShowtimeSeat;
use App\Models\Showtime;
use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $cinema  = $request->get('cinema', '');
        $movie   = $request->get('movie', '');
        $status  = $request->get('status', '');
        $date    = $request->get('date', '');

        $query = Showtime::with(['movie', 'studio.cinema'])
            ->orderBy('start_time', 'desc');

        if ($search) {
            $query->whereHas(
                'movie',
                fn($q) =>
                $q->where('title', 'like', "%{$search}%")
            );
        }

        if ($cinema) {
            $query->whereHas(
                'studio',
                fn($q) =>
                $q->where('cinema_id', $cinema)
            );
        }

        if ($movie) {
            $query->where('movie_id', $movie);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('start_time', $date);
        }

        $showtimes = $query->paginate(15)->withQueryString();

        $cinemas = Cinema::where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'city']);

        $movies = Movie::whereIn('status', ['now_showing', 'coming_soon'])
            ->orderBy('title')->get(['id', 'title']);

        $stats = [
            'total'     => Showtime::count(),
            'open'      => Showtime::where('status', 'open')
                ->where('start_time', '>', now())->count(),
            'today'     => Showtime::whereDate('start_time', today())->count(),
            'full'      => Showtime::where('status', 'full')->count(),
        ];

        return view('admin.showtimes.index', compact(
            'showtimes',
            'cinemas',
            'movies',
            'stats',
            'search',
            'cinema',
            'movie',
            'status',
            'date'
        ));
    }

    public function create(Request $request)
    {
        $cinemas = Cinema::with([
            'studios' => fn($q) => $q->where('is_active', true)->orderBy('order')
        ])
            ->where('is_active', true)
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        $movies = Movie::whereIn('status', ['now_showing', 'coming_soon'])
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'poster', 'status']);

        $selectedMovie  = $request->movie_id
            ? Movie::find($request->movie_id)
            : null;
        $selectedCinema = $request->cinema_id
            ? Cinema::find($request->cinema_id)
            : null;

        return view('admin.showtimes.create', compact(
            'cinemas',
            'movies',
            'selectedMovie',
            'selectedCinema'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateShowtime($request);

        // Cek konflik jadwal di studio yang sama
        $conflict = $this->checkConflict(
            $validated['studio_id'],
            $validated['start_time'],
            $validated['end_time']
        );

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Jadwal bentrok dengan \"{$conflict->movie->title}\" "
                    . "({$conflict->start_time->format('H:i')} - {$conflict->end_time->format('H:i')}) "
                    . "di studio yang sama."
            );
        }

        DB::beginTransaction();

        try {
            // Hitung end_time otomatis jika tidak diisi
            if (empty($validated['end_time'])) {
                $movie = Movie::find($validated['movie_id']);
                $validated['end_time'] = \Carbon\Carbon::parse(
                    $validated['start_time']
                )->addMinutes(($movie->duration ?? 120) + 30)->toDateTimeString();
            }

            $showtime = Showtime::create(array_merge($validated, [
                'available_seats' => 0,
                'booked_seats'    => 0,
                'status'          => 'open',
            ]));

            // Generate showtime_seats dari seat_layouts studio
            $this->generateShowtimeSeats($showtime);

            DB::commit();

            // Redirect: bulk atau single
            if ($request->has('save_and_add_another')) {
                return redirect()
                    ->route('admin.showtimes.create', [
                        'movie_id'  => $validated['movie_id'],
                        'cinema_id' => Studio::find($validated['studio_id'])->cinema_id,
                    ])
                    ->with('success', "Jadwal berhasil ditambahkan. Tambah jadwal lagi.");
            }

            return redirect()
                ->route('admin.showtimes.show', $showtime)
                ->with('success', 'Jadwal tayang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Showtime store failed: ' . $e->getMessage());

            return back()->withInput()->with(
                'error',
                'Gagal menyimpan jadwal: ' . $e->getMessage()
            );
        }
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'movie_id'      => 'required|exists:movies,id',
            'studio_id'     => 'required|exists:studios,id',
            'dates'         => 'required|array|min:1|max:30',
            'dates.*'       => 'date|after_or_equal:today',
            'times'         => 'required|array|min:1|max:8',
            'times.*'       => 'date_format:H:i',
            'price_regular' => 'required|numeric|min:0',
            'price_student' => 'nullable|numeric|min:0',
            'price_senior'  => 'nullable|numeric|min:0',
            'price_vip'     => 'nullable|numeric|min:0',
            'language'      => 'required|in:dub,sub,original',
            'format'        => 'required|in:2d,3d,imax,4dx,imax_3d,dolby',
        ]);

        $movie   = Movie::findOrFail($request->movie_id);
        $studio  = Studio::findOrFail($request->studio_id);
        $created = 0;
        $skipped = 0;
        $errors  = [];

        DB::beginTransaction();

        try {
            foreach ($request->dates as $date) {
                foreach ($request->times as $time) {
                    $startTime = \Carbon\Carbon::parse("{$date} {$time}");
                    $endTime   = (clone $startTime)
                        ->addMinutes(($movie->duration ?? 120) + 30);

                    // Skip jika sudah lewat
                    if ($startTime->lt(now()->addMinutes(30))) {
                        $skipped++;
                        continue;
                    }

                    // Cek konflik
                    if ($this->checkConflict(
                        $studio->id,
                        $startTime->toDateTimeString(),
                        $endTime->toDateTimeString()
                    )) {
                        $skipped++;
                        $errors[] = "{$date} {$time} — Bentrok";
                        continue;
                    }

                    $showtime = Showtime::create([
                        'movie_id'       => $movie->id,
                        'studio_id'      => $studio->id,
                        'start_time'     => $startTime,
                        'end_time'       => $endTime,
                        'price_regular'  => $request->price_regular,
                        'price_student'  => $request->price_student  ?: $request->price_regular,
                        'price_senior'   => $request->price_senior   ?: $request->price_regular,
                        'price_vip'      => $request->price_vip      ?: $request->price_regular,
                        'language'       => $request->language,
                        'format'         => $request->format,
                        'status'         => 'open',
                        'available_seats' => 0,
                        'booked_seats'   => 0,
                    ]);

                    $this->generateShowtimeSeats($showtime);
                    $created++;
                }
            }

            DB::commit();

            $message = "{$created} jadwal berhasil dibuat.";
            if ($skipped > 0) $message .= " {$skipped} dilewati.";

            return redirect()
                ->route('admin.showtimes.index', ['movie' => $request->movie_id])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with(
                'error',
                'Gagal membuat jadwal: ' . $e->getMessage()
            );
        }
    }

    public function show(Showtime $showtime)
    {
        $showtime->load([
            'movie',
            'studio.cinema',
        ]);

        $seatStats = [
            'total'    => $showtime->studio->total_seats,
            'available' => ShowtimeSeat::where('showtime_id', $showtime->id)
                ->where('status', 'available')->count(),
            'locked'   => ShowtimeSeat::where('showtime_id', $showtime->id)
                ->where('status', 'locked')
                ->where('locked_until', '>', now())->count(),
            'booked'   => ShowtimeSeat::where('showtime_id', $showtime->id)
                ->where('status', 'booked')->count(),
        ];

        $orders = \App\Models\CinemaOrder::where('showtime_id', $showtime->id)
            ->with('user')
            ->whereIn('status', ['pending', 'paid'])
            ->latest()
            ->take(10)
            ->get();

        $revenue = \App\Models\CinemaOrder::where('showtime_id', $showtime->id)
            ->where('status', 'paid')
            ->sum('total');

        return view('admin.showtimes.show', compact(
            'showtime',
            'seatStats',
            'orders',
            'revenue'
        ));
    }

    public function edit(Showtime $showtime)
    {
        $cinemas = Cinema::with([
            'studios' => fn($q) => $q->where('is_active', true)->orderBy('order')
        ])
            ->where('is_active', true)
            ->orderBy('city')->orderBy('name')
            ->get();

        $movies = Movie::whereIn('status', ['now_showing', 'coming_soon'])
            ->orderBy('title')
            ->get(['id', 'title', 'duration', 'poster', 'status']);

        $showtime->load(['studio.cinema', 'movie']);

        $hasOrders = \App\Models\CinemaOrder::where('showtime_id', $showtime->id)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();

        return view('admin.showtimes.edit', compact(
            'showtime',
            'cinemas',
            'movies',
            'hasOrders'
        ));
    }

    public function update(Request $request, Showtime $showtime)
    {
        $validated = $this->validateShowtime($request, $showtime->id);

        // Cek konflik (exclude showtime ini sendiri)
        $conflict = $this->checkConflict(
            $validated['studio_id'],
            $validated['start_time'],
            $validated['end_time'],
            $showtime->id
        );

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Jadwal bentrok dengan \"{$conflict->movie->title}\"."
            );
        }

        $showtime->update($validated);

        return redirect()
            ->route('admin.showtimes.show', $showtime)
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Showtime $showtime)
    {
        $request->validate([
            'status' => 'required|in:open,cancelled,ended',
        ]);

        $showtime->update(['status' => $request->status]);

        // Jika cancelled, release semua locked seats
        if ($request->status === 'cancelled') {
            ShowtimeSeat::where('showtime_id', $showtime->id)
                ->where('status', 'locked')
                ->update([
                    'status'            => 'available',
                    'locked_by_user_id' => null,
                    'locked_until'      => null,
                ]);
        }

        return back()->with('success', 'Status jadwal berhasil diubah.');
    }

    public function destroy(Showtime $showtime)
    {
        // Cek ada paid orders
        $hasPaidOrders = \App\Models\CinemaOrder::where('showtime_id', $showtime->id)
            ->where('status', 'paid')
            ->exists();

        if ($hasPaidOrders) {
            return back()->with(
                'error',
                'Jadwal tidak dapat dihapus karena sudah ada tiket yang terbayar.'
            );
        }

        DB::beginTransaction();

        try {
            // Hapus showtime_seats dan orders pending
            \App\Models\CinemaOrder::where('showtime_id', $showtime->id)
                ->whereIn('status', ['pending', 'expired'])
                ->delete();

            ShowtimeSeat::where('showtime_id', $showtime->id)->delete();

            $showtime->delete();

            DB::commit();

            return redirect()
                ->route('admin.showtimes.index')
                ->with('success', 'Jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus jadwal.');
        }
    }

    public function getStudios(Cinema $cinema)
    {
        $studios = $cinema->studios()
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'type', 'total_seats']);

        return response()->json($studios->map(fn($s) => [
            'id'          => $s->id,
            'name'        => $s->name,
            'type'        => $s->type,
            'type_name'   => $s->type_name,
            'total_seats' => $s->total_seats,
        ]));
    }

    public function checkConflictAjax(Request $request)
    {
        $request->validate([
            'studio_id'   => 'required|integer',
            'start_time'  => 'required|date',
            'end_time'    => 'required|date|after:start_time',
            'showtime_id' => 'nullable|integer',
        ]);

        $conflict = $this->checkConflict(
            $request->studio_id,
            $request->start_time,
            $request->end_time,
            $request->showtime_id
        );

        return response()->json([
            'conflict' => (bool) $conflict,
            'message'  => $conflict
                ? "Bentrok dengan \"{$conflict->movie->title}\" ({$conflict->start_time->format('H:i')} - {$conflict->end_time->format('H:i')})"
                : null,
        ]);
    }

    private function validateShowtime(
        Request $request,
        ?int $excludeId = null
    ): array {
        return $request->validate([
            'movie_id'      => 'required|exists:movies,id',
            'studio_id'     => 'required|exists:studios,id',
            'start_time'    => 'required|date',
            'end_time'      => 'required|date|after:start_time',
            'price_regular' => 'required|numeric|min:0',
            'price_student' => 'nullable|numeric|min:0',
            'price_senior'  => 'nullable|numeric|min:0',
            'price_vip'     => 'nullable|numeric|min:0',
            'language'      => 'required|in:dub,sub,original',
            'format'        => 'required|in:2d,3d,imax,4dx,imax_3d,dolby',
            'notes'         => 'nullable|string|max:300',
        ]);
    }

    private function checkConflict(
        int    $studioId,
        string $startTime,
        string $endTime,
        ?int   $excludeId = null
    ): ?Showtime {
        return Showtime::with('movie')
            ->where('studio_id', $studioId)
            ->whereNotIn('status', ['cancelled', 'ended'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time',  [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time',   '>=', $endTime);
                    });
            })
            ->first();
    }

    private function generateShowtimeSeats(Showtime $showtime): void
    {
        $studio = Studio::with('seatLayouts')->find($showtime->studio_id);

        $seats = $studio->seatLayouts->map(fn($layout) => [
            'showtime_id'       => $showtime->id,
            'seat_layout_id'    => $layout->id,
            'locked_by_user_id' => null,
            'status'            => $layout->is_active ? 'available' : 'disabled',
            'locked_until'      => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ])->toArray();

        foreach (array_chunk($seats, 200) as $chunk) {
            ShowtimeSeat::insert($chunk);
        }

        $available = collect($seats)->where('status', 'available')->count();
        $showtime->update(['available_seats' => $available]);
    }
}
