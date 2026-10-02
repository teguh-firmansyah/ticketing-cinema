<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function index(Request $request)
    {
        $city   = $request->get('city');
        $search = $request->get('search');
        $type   = $request->get('type');

        $query = Cinema::with(['studios' => fn($q) => $q->where('is_active', true)])
            ->where('is_active', true);

        if ($city) {
            $query->where('city', $city);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',    'like', "%{$search}%")
                    ->orWhere('city',  'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->whereHas(
                'studios',
                fn($q) =>
                $q->where('type', $type)->where('is_active', true)
            );
        }

        $cinemas = $query->orderBy('order')->get();

        // Kota unik
        $cities = Cinema::where('is_active', true)
            ->distinct('city')
            ->orderBy('city')
            ->pluck('city');

        // Stats
        $stats = [
            'total'   => Cinema::where('is_active', true)->count(),
            'cities'  => $cities->count(),
            'studios' => \App\Models\Studio::where('is_active', true)->count(),
        ];

        // Now showing di masing-masing cinema
        $nowShowingPerCinema = Showtime::with(['movie', 'studio'])
            ->where('status', 'open')
            ->where('start_time', '>', now())
            ->where('start_time', '<', now()->addDays(1))
            ->get()
            ->groupBy(fn($s) => $s->studio->cinema_id);

        return view('cinema.cinemas.index', compact(
            'cinemas',
            'cities',
            'stats',
            'city',
            'search',
            'type',
            'nowShowingPerCinema',
        ));
    }

    public function show(Cinema $cinema, Request $request)
    {
        $cinema->load([
            'studios' => fn($q) => $q->where('is_active', true)->orderBy('order'),
            'studios.seatLayouts',
        ]);

        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        // Validate date
        try {
            $dateCarbon = \Carbon\Carbon::parse($selectedDate);
            if ($dateCarbon->lt(now()->startOfDay())) {
                $selectedDate = now()->format('Y-m-d');
            }
        } catch (\Exception $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        // Showtimes hari ini / tanggal dipilih
        $showtimes = Showtime::with(['movie', 'studio'])
            ->whereHas('studio', fn($q) => $q->where('cinema_id', $cinema->id))
            ->where('status', 'open')
            ->whereDate('start_time', $selectedDate)
            ->orderBy('start_time')
            ->get()
            ->groupBy('movie_id');

        // Semua tanggal yang punya showtime (7 hari)
        $availableDates = Showtime::whereHas(
            'studio',
            fn($q) => $q->where('cinema_id', $cinema->id)
        )
            ->where('status', 'open')
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays(7))
            ->selectRaw('DATE(start_time) as date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->map(fn($d) => [
                'date'     => $d,
                'label'    => \Carbon\Carbon::parse($d)->translatedFormat('D'),
                'day'      => \Carbon\Carbon::parse($d)->format('d'),
                'month'    => \Carbon\Carbon::parse($d)->translatedFormat('M'),
                'is_today' => \Carbon\Carbon::parse($d)->isToday(),
            ]);

        // Film yang tayang di cinema ini
        $movies = Movie::whereHas(
            'showtimes.studio',
            fn($q) => $q->where('cinema_id', $cinema->id)
        )
            ->where('status', 'now_showing')
            ->get();

        return view('cinema.cinemas.show', compact(
            'cinema',
            'showtimes',
            'availableDates',
            'selectedDate',
            'movies',
        ));
    }
}
