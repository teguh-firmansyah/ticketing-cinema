<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $genre  = $request->get('genre', '');

        $query = Movie::withCount([
            'showtimes as total_showtimes' =>
            fn($q) => $q->where('status', 'open')
        ])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title',          'like', "%{$search}%")
                    ->orWhere('original_title', 'like', "%{$search}%")
                    ->orWhere('director',      'like', "%{$search}%");
            });
        }

        if ($status) $query->where('status', $status);

        if ($genre) $query->whereJsonContains('genres', $genre);

        $movies = $query->paginate(12)->withQueryString();

        $genres = Movie::whereNotNull('genres')
            ->pluck('genres')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'total'       => Movie::count(),
            'now_showing' => Movie::where('status', 'now_showing')->count(),
            'coming_soon' => Movie::where('status', 'coming_soon')->count(),
            'ended'       => Movie::where('status', 'ended')->count(),
        ];

        return view('admin.movies.index', compact(
            'movies',
            'genres',
            'stats',
            'search',
            'status',
            'genre'
        ));
    }

    public function create()
    {
        return view('admin.movies.create');
    }

    public function tmdbSearch(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $results = $this->tmdb->searchMovies($request->q);

        // Mark sudah ada di DB
        $existingTmdbIds = Movie::whereIn(
            'tmdb_id',
            collect($results['results'])->pluck('tmdb_id')
        )->pluck('status', 'tmdb_id');

        $results['results'] = array_map(function ($movie) use ($existingTmdbIds) {
            $movie['in_db']     = $existingTmdbIds->has($movie['tmdb_id']);
            $movie['db_status'] = $existingTmdbIds->get($movie['tmdb_id']);
            return $movie;
        }, $results['results']);

        return response()->json($results);
    }

    public function tmdbDetail(int $tmdbId)
    {
        $movie = $this->tmdb->getMovie($tmdbId);

        if (!$movie) {
            return response()->json([
                'error' => 'Film tidak ditemukan di TMDb.'
            ], 404);
        }

        return response()->json($movie);
    }

    public function tmdbBrowse(Request $request)
    {
        $tab  = $request->get('tab', 'now_playing');
        $page = (int) $request->get('page', 1);

        $data = match ($tab) {
            'upcoming' => $this->tmdb->upcoming($page),
            'popular'  => $this->tmdb->popular($page),
            default    => $this->tmdb->nowPlaying($page),
        };

        // Mark existing
        $existing = Movie::whereIn(
            'tmdb_id',
            collect($data['results'])->pluck('tmdb_id')
        )->pluck('status', 'tmdb_id');

        $data['results'] = array_map(function ($movie) use ($existing) {
            $movie['in_db']     = $existing->has($movie['tmdb_id']);
            $movie['db_status'] = $existing->get($movie['tmdb_id']);
            return $movie;
        }, $data['results']);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tmdb_id'        => 'nullable|integer|unique:movies,tmdb_id',
            'title'          => 'required|string|max:200',
            'original_title' => 'nullable|string|max:200',
            'synopsis'       => 'nullable|string',
            'poster'         => 'nullable|string|max:500',
            'backdrop'       => 'nullable|string|max:500',
            'trailer_url'    => 'nullable|url|max:500',
            'genres'         => 'nullable|array',
            'genres.*'       => 'string',
            'duration'       => 'nullable|integer|min:1|max:600',
            'age_rating'     => 'required|in:SU,13+,17+,21+',
            'language'       => 'nullable|string|max:10',
            'director'       => 'nullable|string|max:150',
            'cast'           => 'nullable|array',
            'cast.*'         => 'string',
            'production_companies' => 'nullable|array',
            'release_date'   => 'nullable|date',
            'vote_average'   => 'nullable|numeric|min:0|max:10',
            'vote_count'     => 'nullable|integer|min:0',
            'status'         => 'required|in:coming_soon,now_showing,ended',
            'is_featured'    => 'boolean',
        ]);

        // Poster & backdrop — simpan URL TMDb langsung (tidak download)
        $posterUrl   = $validated['poster']   ?? null;
        $backdropUrl = $validated['backdrop'] ?? null;

        // Upload custom poster jika ada
        if ($request->hasFile('poster_upload')) {
            $posterUrl = $request->file('poster_upload')
                ->store('movies/posters', 'public');
        }

        $movie = Movie::create(array_merge($validated, [
            'slug'     => $this->generateSlug($validated['title']),
            'poster'   => $posterUrl,
            'backdrop' => $backdropUrl,
        ]));

        return redirect()
            ->route('admin.movies.show', $movie)
            ->with('success', "Film \"{$movie->title}\" berhasil ditambahkan.");
    }

    public function importFromTmdb(Request $request)
    {
        $request->validate([
            'tmdb_id'    => 'required|integer|unique:movies,tmdb_id',
            'status'     => 'required|in:coming_soon,now_showing,ended',
            'age_rating' => 'required|in:SU,13+,17+,21+',
        ]);

        $tmdbData = $this->tmdb->getMovie($request->tmdb_id);

        if (!$tmdbData) {
            return back()->with('error', 'Film tidak ditemukan di TMDb.');
        }

        try {
            $movie = Movie::create([
                'tmdb_id'              => $request->tmdb_id,
                'title'                => $tmdbData['title'],
                'original_title'       => $tmdbData['original_title'],
                'slug'                 => $this->generateSlug($tmdbData['title']),
                'synopsis'             => $tmdbData['synopsis'],
                'poster'               => $tmdbData['poster'],
                'backdrop'             => $tmdbData['backdrop'],
                'trailer_url'          => $tmdbData['trailer_url'],
                'genres'               => $tmdbData['genres'],
                'duration'             => $tmdbData['duration'],
                'age_rating'           => $request->age_rating,
                'language'             => $tmdbData['language'] === 'id' ? 'id' : 'en',
                'director'             => $tmdbData['director'],
                'cast'                 => $tmdbData['cast'],
                'production_companies' => $tmdbData['production_companies'],
                'release_date'         => $tmdbData['release_date'],
                'vote_average'         => $tmdbData['vote_average'],
                'vote_count'           => $tmdbData['vote_count'],
                'status'               => $request->status,
                'is_featured'          => false,
            ]);

            return response()->json([
                'success'  => true,
                'movie_id' => $movie->id,
                'title'    => $movie->title,
                'redirect' => route('admin.movies.show', $movie),
            ]);
        } catch (\Exception $e) {
            Log::error('Movie import failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Movie $movie)
    {
        $movie->load([]);

        $showtimes = $movie->showtimes()
            ->with('studio.cinema')
            ->where('start_time', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->take(10)
            ->get();

        $showtimeStats = [
            'total'     => $movie->showtimes()->count(),
            'upcoming'  => $movie->showtimes()->where('start_time', '>=', now())->count(),
            'tickets'   => \App\Models\CinemaTicket::where('movie_title', $movie->title)->count(),
        ];

        return view('admin.movies.show', compact(
            'movie',
            'showtimes',
            'showtimeStats'
        ));
    }

    public function edit(Movie $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:200',
            'original_title' => 'nullable|string|max:200',
            'synopsis'       => 'nullable|string',
            'trailer_url'    => 'nullable|url|max:500',
            'genres'         => 'nullable|array',
            'genres.*'       => 'string',
            'duration'       => 'nullable|integer|min:1|max:600',
            'age_rating'     => 'required|in:SU,13+,17+,21+',
            'language'       => 'nullable|string|max:10',
            'director'       => 'nullable|string|max:150',
            'cast'           => 'nullable|string',
            'release_date'   => 'nullable|date',
            'vote_average'   => 'nullable|numeric|min:0|max:10',
            'status'         => 'required|in:coming_soon,now_showing,ended',
            'is_featured'    => 'boolean',
            'poster_upload'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        // Upload poster baru jika ada
        if ($request->hasFile('poster_upload')) {
            // Hapus poster lama jika bukan URL eksternal
            if ($movie->poster && !str_starts_with($movie->poster, 'http')) {
                Storage::disk('public')->delete($movie->poster);
            }
            $validated['poster'] = $request->file('poster_upload')
                ->store('movies/posters', 'public');
        }

        // Parse cast dari textarea
        if (isset($validated['cast']) && is_string($validated['cast'])) {
            $validated['cast'] = array_filter(
                array_map('trim', explode("\n", $validated['cast']))
            );
        }

        // Update slug jika title berubah
        if ($validated['title'] !== $movie->title) {
            $validated['slug'] = $this->generateSlug($validated['title']);
        }

        $movie->update($validated);

        return redirect()
            ->route('admin.movies.show', $movie)
            ->with('success', 'Film berhasil diperbarui.');
    }

    public function toggleFeatured(Movie $movie)
    {
        $movie->update(['is_featured' => !$movie->is_featured]);

        return back()->with(
            'success',
            $movie->is_featured
                ? "{$movie->title} ditandai sebagai featured."
                : "{$movie->title} dihapus dari featured."
        );
    }

    public function syncFromTmdb(Movie $movie)
    {
        if (!$movie->tmdb_id) {
            return back()->with('error', 'Film ini tidak memiliki TMDb ID.');
        }

        $tmdbData = $this->tmdb->getMovie($movie->tmdb_id);

        if (!$tmdbData) {
            return back()->with('error', 'Gagal mengambil data dari TMDb.');
        }

        $movie->update([
            'title'                => $tmdbData['title'],
            'original_title'       => $tmdbData['original_title'],
            'synopsis'             => $tmdbData['synopsis'],
            'poster'               => $tmdbData['poster'],
            'backdrop'             => $tmdbData['backdrop'],
            'trailer_url'          => $tmdbData['trailer_url'] ?? $movie->trailer_url,
            'genres'               => $tmdbData['genres'],
            'duration'             => $tmdbData['duration'],
            'director'             => $tmdbData['director'],
            'cast'                 => $tmdbData['cast'],
            'production_companies' => $tmdbData['production_companies'],
            'vote_average'         => $tmdbData['vote_average'],
            'vote_count'           => $tmdbData['vote_count'],
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget(
            "tmdb.movie.{$movie->tmdb_id}"
        );

        return back()->with('success', 'Data film berhasil disinkronkan dari TMDb.');
    }

    public function destroy(Movie $movie)
    {
        // Cek ada showtimes aktif
        if ($movie->showtimes()->whereIn('status', ['open', 'full'])->exists()) {
            return back()->with(
                'error',
                'Film tidak dapat dihapus karena masih memiliki jadwal aktif.'
            );
        }

        // Hapus poster lokal
        if ($movie->poster && !str_starts_with($movie->poster, 'http')) {
            Storage::disk('public')->delete($movie->poster);
        }

        $title = $movie->title;
        $movie->forceDelete();

        return redirect()
            ->route('admin.movies.index')
            ->with('success', "Film \"{$title}\" berhasil dihapus.");
    }

    private function generateSlug(string $title): string
    {
        $base = Str::slug($title . '-' . date('Y'));
        $slug = $base;
        $i    = 1;
        while (Movie::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
