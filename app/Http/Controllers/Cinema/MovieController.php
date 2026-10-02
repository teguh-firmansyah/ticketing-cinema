<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'now_showing');
        $genre  = $request->get('genre');
        $rating = $request->get('rating');
        $sort   = $request->get('sort', 'latest');

        // Validasi status
        if (!in_array($status, ['now_showing', 'coming_soon'])) {
            $status = 'now_showing';
        }

        $query = Movie::where('status', $status);

        // Filter genre
        if ($genre) {
            $query->whereJsonContains('genres', $genre);
        }

        // Filter rating
        if ($rating) {
            $query->where('age_rating', $rating);
        }

        // Sort
        $query = match ($sort) {
            'title'    => $query->orderBy('title'),
            'rating'   => $query->orderByDesc('vote_average'),
            'release'  => $query->orderByDesc('release_date'),
            default    => $query->latest(),
        };

        $movies = $query->paginate(12)->withQueryString();

        // Stats
        $stats = [
            'now_showing' => Movie::where('status', 'now_showing')->count(),
            'coming_soon' => Movie::where('status', 'coming_soon')->count(),
        ];

        // Semua genre unik
        $genres = Movie::whereNotNull('genres')
            ->pluck('genres')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        // Featured movies untuk hero (now_showing saja)
        $featured = Movie::where('status', 'now_showing')
            ->where('is_featured', true)
            ->take(5)
            ->get();

        return view('cinema.movies.index', compact(
            'movies',
            'stats',
            'genres',
            'featured',
            'status',
            'genre',
            'rating',
            'sort'
        ));
    }

    public function show(Movie $movie)
    {
        abort_if(
            !in_array($movie->status, ['now_showing', 'coming_soon']),
            404
        );

        $movie->load([]);

        // Tanggal tersedia (7 hari ke depan)
        $selectedDate = request('date', now()->format('Y-m-d'));

        // Validasi date
        try {
            $selectedDateCarbon = \Carbon\Carbon::parse($selectedDate);
            if ($selectedDateCarbon->lt(now()->startOfDay())) {
                $selectedDate = now()->format('Y-m-d');
            }
        } catch (\Exception $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        // Semua tanggal yang punya showtime
        $availableDates = \App\Models\Showtime::where('movie_id', $movie->id)
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

        // Showtimes untuk tanggal yang dipilih,
        // digroup per cinema
        $showtimes = \App\Models\Showtime::with([
            'studio.cinema',
        ])
            ->where('movie_id',   $movie->id)
            ->where('status',     'open')
            ->where('available_seats', '>', 0)
            ->whereDate('start_time', $selectedDate)
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn($s) => $s->studio->cinema->id);

        // Cinema list dari showtimes
        $cinemas = \App\Models\Cinema::whereIn(
            'id',
            $showtimes->keys()
        )->orderBy('order')->get()->keyBy('id');

        // Related movies
        $related = Movie::where('id', '!=', $movie->id)
            ->where(function ($q) use ($movie) {
                foreach (($movie->genres ?? []) as $genre) {
                    $q->orWhereJsonContains('genres', $genre);
                }
            })
            ->whereIn('status', ['now_showing', 'coming_soon'])
            ->take(5)
            ->get();

        return view('cinema.movies.show', compact(
            'movie',
            'showtimes',
            'cinemas',
            'availableDates',
            'selectedDate',
            'related'
        ));
    }

    public function coming(Request $request)
    {
        return redirect()->route('cinema.movies', array_merge(
            $request->all(),
            ['status' => 'coming_soon']
        ));
    }
}
