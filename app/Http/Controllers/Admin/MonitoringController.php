<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\CinemaOrder;
use App\Models\CinemaTicket;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $date    = $request->get('date', today()->format('Y-m-d'));
        $cinema  = $request->get('cinema', '');

        try {
            $selectedDate = Carbon::parse($date);
        } catch (\Exception $e) {
            $selectedDate = today();
            $date         = $selectedDate->format('Y-m-d');
        }

        $cinemas = Cinema::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'city']);

        // Showtimes hari ini per cinema
        $showtimesQuery = Showtime::with([
            'movie:id,title,poster,duration,age_rating',
            'studio:id,cinema_id,name,type,total_seats',
            'studio.cinema:id,name,city',
        ])
            ->whereDate('start_time', $selectedDate)
            ->whereIn('status', ['open', 'full'])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'studio',
                    fn($q2) =>
                    $q2->where('cinema_id', $cinema)
                )
            )
            ->orderBy('start_time');

        $showtimes = $showtimesQuery->get();

        // Summary stats
        $stats = $this->getDayStats($selectedDate, $cinema);

        // Recent transactions (last 20)
        $recentOrders = $this->getRecentOrders($selectedDate, $cinema);

        // Revenue by cinema
        $revenueByMovie = $this->getRevenueByMovie($selectedDate, $cinema);

        return view('admin.monitoring.index', compact(
            'showtimes',
            'cinemas',
            'stats',
            'recentOrders',
            'revenueByMovie',
            'date',
            'cinema',
            'selectedDate'
        ));
    }

    public function realtime(Request $request)
    {
        $date   = $request->get('date', today()->format('Y-m-d'));
        $cinema = $request->get('cinema', '');

        try {
            $selectedDate = Carbon::parse($date);
        } catch (\Exception $e) {
            $selectedDate = today();
        }

        // Showtime statuses
        $showtimes = Showtime::with([
            'movie:id,title,poster',
            'studio:id,cinema_id,name,type,total_seats',
            'studio.cinema:id,name',
        ])
            ->whereDate('start_time', $selectedDate)
            ->whereIn('status', ['open', 'full'])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'studio',
                    fn($q2) =>
                    $q2->where('cinema_id', $cinema)
                )
            )
            ->orderBy('start_time')
            ->get()
            ->map(function ($st) {
                $total     = $st->studio->total_seats ?: 1;
                $booked    = ShowtimeSeat::where('showtime_id', $st->id)
                    ->where('status', 'booked')->count();
                $locked    = ShowtimeSeat::where('showtime_id', $st->id)
                    ->where('status', 'locked')
                    ->where('locked_until', '>', now())->count();
                $available = $total - $booked - $locked;

                return [
                    'id'            => $st->id,
                    'movie_title'   => $st->movie->title,
                    'movie_poster'  => $st->movie->poster_url,
                    'cinema_name'   => $st->studio->cinema->name,
                    'studio_name'   => $st->studio->name,
                    'studio_type'   => $st->studio->type,
                    'start_time'    => $st->start_time->format('H:i'),
                    'end_time'      => $st->end_time->format('H:i'),
                    'is_now'        => $st->start_time->lt(now()) && $st->end_time->gt(now()),
                    'is_upcoming'   => $st->start_time->gt(now()),
                    'format'        => $st->format_label,
                    'language'      => $st->language_label,
                    'total'         => $total,
                    'booked'        => $booked,
                    'locked'        => $locked,
                    'available'     => max(0, $available),
                    'occupancy_pct' => round(($booked / $total) * 100),
                    'status'        => $st->status,
                    'price_regular' => $st->price_regular,
                ];
            });

        // Live stats
        $stats = $this->getDayStats($selectedDate, $cinema);

        // Recent orders (last 10)
        $recentOrders = CinemaOrder::with([
            'user:id,name',
            'showtime.movie:id,title',
            'showtime.studio.cinema:id,name',
        ])
            ->whereDate('created_at', $selectedDate)
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->whereIn('status', ['pending', 'paid'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'user_name'    => $o->user->name,
                'movie_title'  => $o->showtime->movie->title,
                'cinema_name'  => $o->showtime->studio->cinema->name,
                'total'        => $o->total,
                'status'       => $o->status,
                'status_label' => $o->status_label,
                'seats'        => $o->seats_string,
                'created_at'   => $o->created_at->format('H:i:s'),
                'time_ago'     => $o->created_at->diffForHumans(),
            ]);

        return response()->json([
            'showtimes'    => $showtimes,
            'stats'        => $stats,
            'recent_orders' => $recentOrders,
            'server_time'  => now()->format('H:i:s'),
            'timestamp'    => now()->timestamp,
        ]);
    }

    public function showtimeSeatMap(Showtime $showtime)
    {
        $showtime->load(['movie', 'studio.cinema']);

        $seats = ShowtimeSeat::where('showtime_id', $showtime->id)
            ->with(['seatLayout', 'lockedByUser:id,name'])
            ->get()
            ->groupBy(fn($s) => $s->seatLayout->row_label)
            ->map(
                fn($row) => $row->sortBy('seatLayout.col_number')
                    ->map(fn($seat) => [
                        'id'          => $seat->id,
                        'seat_number' => $seat->seatLayout->seat_number,
                        'row_label'   => $seat->seatLayout->row_label,
                        'col_number'  => $seat->seatLayout->col_number,
                        'seat_type'   => $seat->seatLayout->seat_type,
                        'status'      => $seat->effective_status,
                        'locked_by'   => $seat->lockedByUser?->name,
                        'locked_until' => $seat->locked_until?->format('H:i:s'),
                    ])
                    ->values()
            );

        return response()->json([
            'showtime' => [
                'id'          => $showtime->id,
                'movie_title' => $showtime->movie->title,
                'studio_name' => $showtime->studio->name,
                'cinema_name' => $showtime->studio->cinema->name,
                'start_time'  => $showtime->start_time->format('H:i'),
                'end_time'    => $showtime->end_time->format('H:i'),
                'format'      => $showtime->format_label,
            ],
            'seats'    => $seats,
            'stats'    => [
                'total'    => $showtime->studio->total_seats,
                'booked'   => ShowtimeSeat::where('showtime_id', $showtime->id)->where('status', 'booked')->count(),
                'locked'   => ShowtimeSeat::where('showtime_id', $showtime->id)->where('status', 'locked')->where('locked_until', '>', now())->count(),
                'available' => ShowtimeSeat::where('showtime_id', $showtime->id)->where('status', 'available')->count(),
            ],
        ]);
    }

    private function getDayStats(Carbon $date, string $cinema = ''): array
    {
        $orderQuery = CinemaOrder::whereDate('created_at', $date);
        $ticketQuery = CinemaTicket::whereDate('created_at', $date);

        if ($cinema) {
            $orderQuery->whereHas(
                'showtime.studio',
                fn($q) => $q->where('cinema_id', $cinema)
            );
            $ticketQuery->whereHas(
                'orderItem.cinemaOrder.showtime.studio',
                fn($q) => $q->where('cinema_id', $cinema)
            );
        }

        $paidOrders = (clone $orderQuery)->where('status', 'paid');

        return [
            'total_orders'  => (clone $orderQuery)->count(),
            'paid_orders'   => (clone $paidOrders)->count(),
            'pending_orders' => (clone $orderQuery)->where('status', 'pending')->count(),
            'revenue'       => (clone $paidOrders)->sum('total'),
            'tickets_sold'  => (clone $ticketQuery)->where('status', 'active')->count(),
            'active_shows'  => Showtime::whereDate('start_time', $date)
                ->where('start_time', '<', now())
                ->where('end_time',   '>', now())
                ->when(
                    $cinema,
                    fn($q) =>
                    $q->whereHas(
                        'studio',
                        fn($q2) =>
                        $q2->where('cinema_id', $cinema)
                    )
                )
                ->count(),
            'upcoming_shows' => Showtime::whereDate('start_time', $date)
                ->where('start_time', '>', now())
                ->when(
                    $cinema,
                    fn($q) =>
                    $q->whereHas(
                        'studio',
                        fn($q2) =>
                        $q2->where('cinema_id', $cinema)
                    )
                )
                ->count(),
        ];
    }

    private function getRecentOrders(Carbon $date, string $cinema = ''): object
    {
        return CinemaOrder::with([
            'user:id,name,email',
            'showtime.movie:id,title,poster',
            'showtime.studio.cinema:id,name',
            'orderItems',
        ])
            ->whereDate('created_at', $date)
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->whereIn('status', ['pending', 'paid'])
            ->latest()
            ->take(20)
            ->get();
    }

    private function getRevenueByMovie(Carbon $date, string $cinema = ''): object
    {
        return CinemaOrder::select(
            DB::raw('movies.title as movie_title'),
            DB::raw('movies.poster as movie_poster'),
            DB::raw('COUNT(cinema_orders.id) as total_orders'),
            DB::raw('SUM(cinema_orders.total) as total_revenue'),
            DB::raw('COUNT(cinema_order_items.id) as total_tickets')
        )
            ->join('showtimes', 'showtimes.id', '=', 'cinema_orders.showtime_id')
            ->join('movies',    'movies.id',    '=', 'showtimes.movie_id')
            ->join('cinema_order_items', 'cinema_order_items.cinema_order_id', '=', 'cinema_orders.id')
            ->where('cinema_orders.status', 'paid')
            ->whereDate('cinema_orders.created_at', $date)
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->groupBy('movies.id', 'movies.title', 'movies.poster')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();
    }
}
