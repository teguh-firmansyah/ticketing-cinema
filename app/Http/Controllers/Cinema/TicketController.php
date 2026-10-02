<?php

namespace App\Http\Controllers\Cinema;

use App\Http\Controllers\Controller;
use App\Models\CinemaTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'upcoming');
        $search = $request->get('search', '');
        $order  = $request->get('order');

        $baseQuery = CinemaTicket::where('user_id', auth()->id())
            ->with([
                'orderItem.cinemaOrder.showtime.movie',
                'orderItem.cinemaOrder.showtime.studio.cinema',
            ]);

        // Filter tab
        $query = match ($tab) {
            'used'      => (clone $baseQuery)->where('status', 'used'),
            'cancelled' => (clone $baseQuery)->whereIn('status', ['cancelled', 'expired']),
            default     => (clone $baseQuery)
                ->where('status', 'active')
                ->where('show_time', '>=', now()->startOfDay()),
        };

        // Filter search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code',  'like', "%{$search}%")
                    ->orWhere('movie_title', 'like', "%{$search}%")
                    ->orWhere('seat_number', 'like', "%{$search}%");
            });
        }

        // Filter by order
        if ($order) {
            $query->whereHas(
                'orderItem.cinemaOrder',
                fn($q) => $q->where('id', $order)
            );
        }

        $tickets = $query
            ->orderBy('show_time', $tab === 'upcoming' ? 'asc' : 'desc')
            ->paginate(9)
            ->withQueryString();

        // Stats
        $stats = [
            'upcoming'  => CinemaTicket::where('user_id', auth()->id())
                ->where('status', 'active')
                ->where('show_time', '>=', now()->startOfDay())
                ->count(),
            'used'      => CinemaTicket::where('user_id', auth()->id())
                ->where('status', 'used')->count(),
            'cancelled' => CinemaTicket::where('user_id', auth()->id())
                ->whereIn('status', ['cancelled', 'expired'])->count(),
        ];

        return view('cinema.tickets.index', compact(
            'tickets',
            'stats',
            'tab',
            'search'
        ));
    }

    public function show(CinemaTicket $ticket)
    {
        abort_if($ticket->user_id !== auth()->id(), 403);

        $ticket->load([
            'orderItem.cinemaOrder.showtime.movie',
            'orderItem.cinemaOrder.showtime.studio.cinema',
        ]);

        return view('cinema.tickets.show', compact('ticket'));
    }

    // QR code raw image (untuk download)
    public function qr(CinemaTicket $ticket)
    {
        abort_if($ticket->user_id !== auth()->id(), 403);

        if (!$ticket->qr_code) {
            abort(404);
        }

        $path = storage_path('app/public/' . $ticket->qr_code);
        abort_if(!file_exists($path), 404);

        return response()->file($path, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'inline; filename="ticket-' . $ticket->ticket_code . '.png"',
        ]);
    }
}
