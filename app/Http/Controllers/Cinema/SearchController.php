<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['movies' => []]);
        }

        $movies = Movie::where(function ($query) use ($q) {
            $query->where('title', 'like', "%{$q}%")
                ->orWhere('original_title', 'like', "%{$q}%")
                ->orWhere('director', 'like', "%{$q}%")
                ->orWhereJsonContains('genres', $q);
        })
            ->whereIn('status', ['now_showing', 'coming_soon'])
            ->take(6)
            ->get()
            ->map(fn($movie) => [
                'id'     => $movie->id,
                'title'  => $movie->title,
                'genre'  => $movie->genres_string,
                'poster' => $movie->poster_url,
                'status' => $movie->status,
                'url'    => route('cinema.movies.show', $movie->slug),
            ]);

        return response()->json(['movies' => $movies]);
    }
}
