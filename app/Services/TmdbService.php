<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $imageUrl;

    public function __construct()
    {
        $this->apiKey   = config('services.tmdb.api_key');
        $this->baseUrl  = config('services.tmdb.base_url');
        $this->imageUrl = config('services.tmdb.image_url');
    }

    public function searchMovies(string $query, int $page = 1): array
    {
        $cacheKey = "tmdb.search." . md5($query . $page);

        return Cache::remember($cacheKey, 300, function () use ($query, $page) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/search/movie", [
                    'api_key'       => $this->apiKey,
                    'query'         => $query,
                    'page'          => $page,
                    'language'      => 'id-ID',
                    'include_adult' => false,
                ]);

                if (!$response->successful()) {
                    Log::warning('TMDb search failed', [
                        'status' => $response->status(),
                        'query'  => $query,
                    ]);
                    return ['results' => [], 'total_results' => 0, 'total_pages' => 0];
                }

                $data = $response->json();

                return [
                    'results'       => array_map(
                        fn($m) => $this->formatMovie($m),
                        $data['results'] ?? []
                    ),
                    'total_results' => $data['total_results'] ?? 0,
                    'total_pages'   => $data['total_pages']   ?? 0,
                ];
            } catch (\Exception $e) {
                Log::error('TMDb search error: ' . $e->getMessage());
                return ['results' => [], 'total_results' => 0, 'total_pages' => 0];
            }
        });
    }

    public function getMovie(int $tmdbId): ?array
    {
        $cacheKey = "tmdb.movie.{$tmdbId}";

        return Cache::remember($cacheKey, 3600, function () use ($tmdbId) {
            try {
                $response = Http::timeout(10)->get(
                    "{$this->baseUrl}/movie/{$tmdbId}",
                    [
                        'api_key'            => $this->apiKey,
                        'language'           => 'id-ID',
                        'append_to_response' => 'credits,videos,recommendations',
                    ]
                );

                if (!$response->successful()) return null;

                return $this->formatMovieDetail($response->json());
            } catch (\Exception $e) {
                Log::error('TMDb getMovie error: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function nowPlaying(int $page = 1): array
    {
        $cacheKey = "tmdb.now_playing.{$page}";

        return Cache::remember($cacheKey, 1800, function () use ($page) {
            try {
                $response = Http::timeout(10)->get(
                    "{$this->baseUrl}/movie/now_playing",
                    [
                        'api_key'  => $this->apiKey,
                        'language' => 'id-ID',
                        'region'   => 'ID',
                        'page'     => $page,
                    ]
                );

                if (!$response->successful()) return ['results' => [], 'total_pages' => 0];

                $data = $response->json();

                return [
                    'results'     => array_map(
                        fn($m) => $this->formatMovie($m),
                        $data['results'] ?? []
                    ),
                    'total_pages' => $data['total_pages'] ?? 0,
                ];
            } catch (\Exception $e) {
                Log::error('TMDb nowPlaying error: ' . $e->getMessage());
                return ['results' => [], 'total_pages' => 0];
            }
        });
    }

    public function upcoming(int $page = 1): array
    {
        $cacheKey = "tmdb.upcoming.{$page}";

        return Cache::remember($cacheKey, 1800, function () use ($page) {
            try {
                $response = Http::timeout(10)->get(
                    "{$this->baseUrl}/movie/upcoming",
                    [
                        'api_key'  => $this->apiKey,
                        'language' => 'id-ID',
                        'region'   => 'ID',
                        'page'     => $page,
                    ]
                );

                if (!$response->successful()) return ['results' => [], 'total_pages' => 0];

                $data = $response->json();

                return [
                    'results'     => array_map(
                        fn($m) => $this->formatMovie($m),
                        $data['results'] ?? []
                    ),
                    'total_pages' => $data['total_pages'] ?? 0,
                ];
            } catch (\Exception $e) {
                Log::error('TMDb upcoming error: ' . $e->getMessage());
                return ['results' => [], 'total_pages' => 0];
            }
        });
    }

    public function popular(int $page = 1): array
    {
        $cacheKey = "tmdb.popular.{$page}";

        return Cache::remember($cacheKey, 1800, function () use ($page) {
            try {
                $response = Http::timeout(10)->get(
                    "{$this->baseUrl}/movie/popular",
                    [
                        'api_key'  => $this->apiKey,
                        'language' => 'id-ID',
                        'page'     => $page,
                    ]
                );

                if (!$response->successful()) return ['results' => [], 'total_pages' => 0];

                $data = $response->json();

                return [
                    'results'     => array_map(
                        fn($m) => $this->formatMovie($m),
                        $data['results'] ?? []
                    ),
                    'total_pages' => $data['total_pages'] ?? 0,
                ];
            } catch (\Exception $e) {
                Log::error('TMDb popular error: ' . $e->getMessage());
                return ['results' => [], 'total_pages' => 0];
            }
        });
    }

    public function posterUrl(?string $path, string $size = 'w500'): string
    {
        if (!$path) return asset('images/no-poster.png');
        return "{$this->imageUrl}/{$size}{$path}";
    }

    public function backdropUrl(?string $path, string $size = 'w1280'): string
    {
        if (!$path) return asset('images/no-backdrop.jpg');
        return "{$this->imageUrl}/{$size}{$path}";
    }

    private function formatMovie(array $movie): array
    {
        return [
            'tmdb_id'        => $movie['id'],
            'title'          => $movie['title']          ?? '',
            'original_title' => $movie['original_title'] ?? '',
            'synopsis'       => $movie['overview']       ?? '',
            'poster'         => $this->posterUrl($movie['poster_path']   ?? null),
            'backdrop'       => $this->backdropUrl($movie['backdrop_path'] ?? null),
            'poster_path'    => $movie['poster_path']    ?? null,
            'backdrop_path'  => $movie['backdrop_path']  ?? null,
            'release_date'   => $movie['release_date']   ?? null,
            'vote_average'   => round($movie['vote_average'] ?? 0, 1),
            'vote_count'     => $movie['vote_count']     ?? 0,
            'genres'         => $this->resolveGenres($movie['genre_ids'] ?? []),
            'popularity'     => $movie['popularity']     ?? 0,
        ];
    }

    private function formatMovieDetail(array $movie): array
    {
        // Extract director
        $director = collect($movie['credits']['crew'] ?? [])
            ->firstWhere('job', 'Director');

        // Extract cast (top 10)
        $cast = collect($movie['credits']['cast'] ?? [])
            ->take(10)
            ->pluck('name')
            ->toArray();

        // Extract trailer (YouTube)
        $trailer = collect($movie['videos']['results'] ?? [])
            ->filter(
                fn($v) =>
                $v['site'] === 'YouTube' &&
                    in_array($v['type'], ['Trailer', 'Teaser'])
            )
            ->sortByDesc('size')
            ->first();

        // Production companies
        $companies = collect($movie['production_companies'] ?? [])
            ->take(3)
            ->pluck('name')
            ->toArray();

        // Genres
        $genres = collect($movie['genres'] ?? [])
            ->pluck('name')
            ->toArray();

        return [
            'tmdb_id'              => $movie['id'],
            'title'                => $movie['title']          ?? '',
            'original_title'       => $movie['original_title'] ?? '',
            'synopsis'             => $movie['overview']       ?? '',
            'poster'               => $this->posterUrl($movie['poster_path']   ?? null),
            'backdrop'             => $this->backdropUrl($movie['backdrop_path'] ?? null),
            'poster_path'          => $movie['poster_path']    ?? null,
            'backdrop_path'        => $movie['backdrop_path']  ?? null,
            'trailer_url'          => $trailer
                ? "https://www.youtube.com/watch?v={$trailer['key']}"
                : null,
            'genres'               => $genres,
            'duration'             => $movie['runtime']        ?? null,
            'language'             => $movie['original_language'] ?? 'id',
            'director'             => $director['name']        ?? null,
            'cast'                 => $cast,
            'production_companies' => $companies,
            'release_date'         => $movie['release_date']   ?? null,
            'vote_average'         => round($movie['vote_average'] ?? 0, 1),
            'vote_count'           => $movie['vote_count']     ?? 0,
        ];
    }

    private function resolveGenres(array $ids): array
    {
        $genreMap = [
            28    => 'Action',
            12    => 'Adventure',
            16    => 'Animation',
            35    => 'Comedy',
            80    => 'Crime',
            99    => 'Documentary',
            18    => 'Drama',
            10751 => 'Family',
            14    => 'Fantasy',
            36    => 'History',
            27    => 'Horror',
            10402 => 'Music',
            9648  => 'Mystery',
            10749 => 'Romance',
            878   => 'Sci-Fi',
            10770 => 'TV Movie',
            53    => 'Thriller',
            10752 => 'War',
            37    => 'Western',
        ];

        return array_values(array_filter(
            array_map(fn($id) => $genreMap[$id] ?? null, $ids)
        ));
    }
}
