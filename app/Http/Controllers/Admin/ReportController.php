<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\CinemaOrder;
use App\Models\CinemaOrderItem;
use App\Models\CinemaTicket;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period  = $request->get('period', 'this_month');
        $cinema  = $request->get('cinema', '');
        $movie   = $request->get('movie', '');

        [$startDate, $endDate] = $this->resolvePeriod(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $cinemas = Cinema::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'city']);

        $movies = Movie::orderBy('title')
            ->get(['id', 'title']);

        $summary = $this->getSummary(
            $startDate,
            $endDate,
            $cinema,
            $movie
        );

        $revenueTrend = $this->getRevenueTrend(
            $startDate,
            $endDate,
            $cinema,
            $movie
        );

        $topMovies = $this->getTopMovies(
            $startDate,
            $endDate,
            $cinema
        );

        $topCinemas = $this->getTopCinemas(
            $startDate,
            $endDate,
            $movie
        );

        $revenueByFormat = $this->getRevenueByFormat(
            $startDate,
            $endDate,
            $cinema,
            $movie
        );

        $revenueByTicketType = $this->getRevenueByTicketType(
            $startDate,
            $endDate,
            $cinema,
            $movie
        );

        $dailyTransactions = $this->getDailyTransactions(
            $startDate,
            $endDate,
            $cinema,
            $movie
        );

        $diffDays  = $startDate->diffInDays($endDate) + 1;
        $prevStart = $startDate->copy()->subDays($diffDays);
        $prevEnd   = $endDate->copy()->subDays($diffDays);
        $prevSummary = $this->getSummary(
            $prevStart,
            $prevEnd,
            $cinema,
            $movie
        );

        return view('admin.reports.index', compact(
            'summary',
            'revenueTrend',
            'topMovies',
            'topCinemas',
            'revenueByFormat',
            'revenueByTicketType',
            'dailyTransactions',
            'cinemas',
            'movies',
            'prevSummary',
            'period',
            'cinema',
            'movie',
            'startDate',
            'endDate'
        ));
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $cinema = $request->get('cinema', '');
        $movie  = $request->get('movie', '');

        [$startDate, $endDate] = $this->resolvePeriod(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $orders = CinemaOrder::with([
            'user:id,name,email',
            'showtime.movie:id,title',
            'showtime.studio:id,name,cinema_id',
            'showtime.studio.cinema:id,name,city',
            'orderItems',
            'tickets',
        ])
            ->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ])
            ->where('status', 'paid')
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->when(
                $movie,
                fn($q) =>
                $q->whereHas(
                    'showtime',
                    fn($q2) =>
                    $q2->where('movie_id', $movie)
                )
            )
            ->orderBy('created_at')
            ->get();

        $filename = 'laporan-bioskop-'
            . $startDate->format('Ymd') . '-'
            . $endDate->format('Ymd')
            . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            // BOM untuk Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'No. Order',
                'Tanggal',
                'Waktu',
                'Pelanggan',
                'Email',
                'Film',
                'Bioskop',
                'Studio',
                'Jam Tayang',
                'Format',
                'Bahasa',
                'Jumlah Tiket',
                'Kursi',
                'Total (Rp)',
                'Status',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('d/m/Y'),
                    $order->created_at->format('H:i:s'),
                    $order->user->name,
                    $order->user->email,
                    $order->showtime->movie->title,
                    $order->showtime->studio->cinema->name,
                    $order->showtime->studio->name,
                    $order->showtime->start_time->format('d/m/Y H:i'),
                    $order->showtime->format_label,
                    $order->showtime->language_label,
                    $order->orderItems->count(),
                    $order->seats_string,
                    number_format($order->total, 0, ',', '.'),
                    'Lunas',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function chartData(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $cinema = $request->get('cinema', '');
        $movie  = $request->get('movie', '');

        [$startDate, $endDate] = $this->resolvePeriod(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        return response()->json([
            'revenue_trend'        => $this->getRevenueTrend($startDate, $endDate, $cinema, $movie),
            'revenue_by_format'    => $this->getRevenueByFormat($startDate, $endDate, $cinema, $movie),
            'revenue_by_ticket_type' => $this->getRevenueByTicketType($startDate, $endDate, $cinema, $movie),
        ]);
    }

    private function resolvePeriod(
        string  $period,
        ?string $startDate,
        ?string $endDate
    ): array {
        return match ($period) {
            'today'        => [today(), today()],
            'yesterday'    => [today()->subDay(), today()->subDay()],
            'this_week'    => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week'    => [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ],
            'this_month'   => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month'   => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ],
            'this_year'    => [now()->startOfYear(), now()->endOfYear()],
            'custom'       => [
                Carbon::parse($startDate ?? now()->startOfMonth()),
                Carbon::parse($endDate   ?? now()),
            ],
            default        => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function getSummary(
        Carbon $start,
        Carbon $end,
        string $cinema,
        string $movie
    ): array {
        $base = CinemaOrder::whereBetween('created_at', [
            $start->copy()->startOfDay(),
            $end->copy()->endOfDay(),
        ])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->when(
                $movie,
                fn($q) =>
                $q->whereHas(
                    'showtime',
                    fn($q2) => $q2->where('movie_id', $movie)
                )
            );

        $paid    = (clone $base)->where('status', 'paid');
        $tickets = CinemaTicket::whereBetween('created_at', [
            $start->copy()->startOfDay(),
            $end->copy()->endOfDay(),
        ])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'orderItem.cinemaOrder.showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->when(
                $movie,
                fn($q) =>
                $q->where('movie_title', function ($q2) use ($movie) {
                    $q2->select('title')
                        ->from('movies')
                        ->where('id', $movie);
                })
            );

        $avgOrderValue = (clone $paid)->avg('total') ?? 0;

        return [
            'total_revenue'    => (clone $paid)->sum('total'),
            'total_orders'     => (clone $paid)->count(),
            'total_tickets'    => (clone $tickets)->where('status', '!=', 'cancelled')->count(),
            'avg_order_value'  => round($avgOrderValue),
            'cancelled_orders' => (clone $base)->whereIn('status', ['cancelled', 'expired'])->count(),
            'pending_orders'   => (clone $base)->where('status', 'pending')->count(),
            'unique_customers' => (clone $paid)->distinct('user_id')->count('user_id'),
            'total_showtimes'  => Showtime::whereBetween('start_time', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
                ->when(
                    $cinema,
                    fn($q) =>
                    $q->whereHas(
                        'studio',
                        fn($q2) => $q2->where('cinema_id', $cinema)
                    )
                )
                ->whereIn('status', ['open', 'full', 'ended'])
                ->count(),
        ];
    }

    private function getRevenueTrend(
        Carbon $start,
        Carbon $end,
        string $cinema,
        string $movie
    ): array {
        $diffDays = $start->diffInDays($end);

        // Jika range > 60 hari, group by week
        $groupBy = $diffDays > 60 ? 'week' : 'day';

        $results = CinemaOrder::select(
            DB::raw(
                $groupBy === 'day'
                    ? 'DATE(created_at) as period'
                    : 'YEARWEEK(created_at, 1) as period'
            ),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders'),
            DB::raw('MIN(DATE(created_at)) as period_start')
        )
            ->whereBetween('created_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->where('status', 'paid')
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->when(
                $movie,
                fn($q) =>
                $q->whereHas(
                    'showtime',
                    fn($q2) => $q2->where('movie_id', $movie)
                )
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Fill missing dates
        $data     = [];
        $current  = $start->copy();

        if ($groupBy === 'day') {
            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');
                $found   = $results->firstWhere('period', $dateStr);
                $data[]  = [
                    'label'   => $current->translatedFormat('D d M'),
                    'date'    => $dateStr,
                    'revenue' => (int) ($found?->revenue ?? 0),
                    'orders'  => (int) ($found?->orders  ?? 0),
                ];
                $current->addDay();
            }
        } else {
            foreach ($results as $r) {
                $data[] = [
                    'label'   => 'W' . substr($r->period, 4)
                        . ' ' . Carbon::parse($r->period_start)
                        ->translatedFormat('d M'),
                    'date'    => $r->period_start,
                    'revenue' => (int) $r->revenue,
                    'orders'  => (int) $r->orders,
                ];
            }
        }

        return $data;
    }
    private function getTopMovies(
        Carbon $start,
        Carbon $end,
        string $cinema
    ): object {
        return CinemaOrder::select(
            'movies.id as movie_id',
            'movies.title as movie_title',
            'movies.poster as movie_poster',
            DB::raw('SUM(cinema_orders.total) as revenue'),
            DB::raw('COUNT(DISTINCT cinema_orders.id) as orders'),
            DB::raw('COUNT(cinema_order_items.id) as tickets')
        )
            ->join('showtimes',          'showtimes.id',          '=', 'cinema_orders.showtime_id')
            ->join('movies',             'movies.id',             '=', 'showtimes.movie_id')
            ->join('cinema_order_items', 'cinema_order_items.cinema_order_id', '=', 'cinema_orders.id')
            ->where('cinema_orders.status', 'paid')
            ->whereBetween('cinema_orders.created_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->groupBy('movies.id', 'movies.title', 'movies.poster')
            ->orderByDesc('revenue')
            ->take(8)
            ->get();
    }

    private function getTopCinemas(
        Carbon $start,
        Carbon $end,
        string $movie
    ): object {
        return CinemaOrder::select(
            'cinemas.id as cinema_id',
            'cinemas.name as cinema_name',
            'cinemas.city as cinema_city',
            DB::raw('SUM(cinema_orders.total) as revenue'),
            DB::raw('COUNT(DISTINCT cinema_orders.id) as orders'),
            DB::raw('COUNT(cinema_order_items.id) as tickets')
        )
            ->join('showtimes',          'showtimes.id',    '=', 'cinema_orders.showtime_id')
            ->join('studios',            'studios.id',      '=', 'showtimes.studio_id')
            ->join('cinemas',            'cinemas.id',      '=', 'studios.cinema_id')
            ->join('cinema_order_items', 'cinema_order_items.cinema_order_id', '=', 'cinema_orders.id')
            ->where('cinema_orders.status', 'paid')
            ->whereBetween('cinema_orders.created_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->when(
                $movie,
                fn($q) =>
                $q->whereHas(
                    'showtime',
                    fn($q2) => $q2->where('movie_id', $movie)
                )
            )
            ->groupBy('cinemas.id', 'cinemas.name', 'cinemas.city')
            ->orderByDesc('revenue')
            ->take(6)
            ->get();
    }

    private function getRevenueByFormat(
        Carbon $start,
        Carbon $end,
        string $cinema,
        string $movie
    ): object {
        return CinemaOrder::select(
            'showtimes.format',
            DB::raw('SUM(cinema_orders.total) as revenue'),
            DB::raw('COUNT(DISTINCT cinema_orders.id) as orders'),
            DB::raw('COUNT(cinema_order_items.id) as tickets')
        )
            ->join('showtimes',          'showtimes.id',     '=', 'cinema_orders.showtime_id')
            ->join('cinema_order_items', 'cinema_order_items.cinema_order_id', '=', 'cinema_orders.id')
            ->where('cinema_orders.status', 'paid')
            ->whereBetween('cinema_orders.created_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->when(
                $movie,
                fn($q) =>
                $q->whereHas(
                    'showtime',
                    fn($q2) => $q2->where('movie_id', $movie)
                )
            )
            ->groupBy('showtimes.format')
            ->orderByDesc('revenue')
            ->get();
    }

    private function getRevenueByTicketType(
        Carbon $start,
        Carbon $end,
        string $cinema,
        string $movie
    ): object {
        return CinemaOrderItem::select(
            'ticket_type',
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(*) as tickets')
        )
            ->whereHas('cinemaOrder', function ($q) use ($start, $end, $cinema, $movie) {
                $q->where('status', 'paid')
                    ->whereBetween('created_at', [
                        $start->copy()->startOfDay(),
                        $end->copy()->endOfDay(),
                    ])
                    ->when(
                        $cinema,
                        fn($q2) =>
                        $q2->whereHas(
                            'showtime.studio',
                            fn($q3) => $q3->where('cinema_id', $cinema)
                        )
                    )
                    ->when(
                        $movie,
                        fn($q2) =>
                        $q2->whereHas(
                            'showtime',
                            fn($q3) => $q3->where('movie_id', $movie)
                        )
                    );
            })
            ->groupBy('ticket_type')
            ->orderByDesc('revenue')
            ->get();
    }

    private function getDailyTransactions(
        Carbon $start,
        Carbon $end,
        string $cinema,
        string $movie
    ): object {
        return CinemaOrder::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_orders'),
            DB::raw('COUNT(CASE WHEN status IN ("cancelled","expired") THEN 1 END) as cancelled_orders'),
            DB::raw('SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as revenue'),
            DB::raw('COUNT(DISTINCT CASE WHEN status = "paid" THEN user_id END) as customers')
        )
            ->whereBetween('created_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->when(
                $cinema,
                fn($q) =>
                $q->whereHas(
                    'showtime.studio',
                    fn($q2) => $q2->where('cinema_id', $cinema)
                )
            )
            ->when(
                $movie,
                fn($q) =>
                $q->whereHas(
                    'showtime',
                    fn($q2) => $q2->where('movie_id', $movie)
                )
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('date')
            ->take(30)
            ->get();
    }

    private function growthPct(float $current, float $previous): float
    {
        if ($previous == 0) return 100;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
