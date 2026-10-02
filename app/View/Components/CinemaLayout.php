<?php

namespace App\View\Components;

use App\Models\Movie;
use Illuminate\View\Component;
use Illuminate\View\View;

class CinemaLayout extends Component
{
    public $nowPlayingMovies;

    public function __construct()
    {
        $this->nowPlayingMovies = Movie::where('status', 'now_showing')
            ->select(['id', 'title', 'slug', 'duration'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function render(): View
    {
        return view('layouts.cinema', [
            'nowPlayingMovies' => $this->nowPlayingMovies,
        ]);
    }
}
