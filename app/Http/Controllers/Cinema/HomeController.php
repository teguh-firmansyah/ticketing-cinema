<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;

class HomeController extends Controller
{
    public function index()
    {
        $featured     = Movie::where('status', 'now_showing')
            ->where('is_featured', true)
            ->take(5)
            ->get();

        $nowShowing   = Movie::where('status', 'now_showing')
            ->latest()
            ->take(8)
            ->get();

        $comingSoon   = Movie::where('status', 'coming_soon')
            ->orderBy('release_date')
            ->take(6)
            ->get();

        $cinemas      = Cinema::where('is_active', true)
            ->withCount('studios')
            ->orderBy('order')
            ->take(6)
            ->get();

        $stats = [
            'movies'   => Movie::whereIn('status', ['now_showing', 'coming_soon'])->count(),
            'cinemas'  => Cinema::where('is_active', true)->count(),
            'cities'   => Cinema::where('is_active', true)->distinct('city')->count('city'),
        ];

        return view('cinema.home', compact(
            'featured',
            'nowShowing',
            'comingSoon',
            'cinemas',
            'stats'
        ));
    }
}
