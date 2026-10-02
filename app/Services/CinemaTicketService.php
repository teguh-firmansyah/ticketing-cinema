<?php

namespace App\Services;

use App\Models\CinemaOrder;
use App\Models\CinemaOrderItem;
use App\Models\CinemaTicket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CinemaTicketService
{
    // Generate tiket untuk semua item dalam ordet
    public function generateTickets(CinemaOrder $order): array
    {
        $order->load([
            'user',
            'showtime.movie',
            'showtime.studio.cinema',
            'orderItems.showtimeSeat.seatLayout',
        ]);

        $tickets = [];

        foreach ($order->orderItems as $item) {
            // Skip jika tiket sudah ada
            if ($item->ticket()->exists()) {
                $tickets[] = $item->ticket;
                continue;
            }

            $ticket     = $this->generateSingleTicket($order, $item);
            $tickets[]  = $ticket;

            Log::info('Cinema ticket generated', [
                'ticket_code' => $ticket->ticket_code,
                'seat'        => $ticket->seat_number,
                'movie'       => $ticket->movie_title,
            ]);
        }

        return $tickets;
    }

    // Generate satu tiket
    private function generateSingleTicket(
        CinemaOrder $order,
        CinemaOrderItem $item
    ): CinemaTicket {
        $showtime = $order->showtime;
        $movie    = $showtime->movie;
        $studio   = $showtime->studio;
        $cinema   = $studio->cinema;
        $seat     = $item->showtimeSeat->seatLayout;

        $ticketCode = $this->generateTicketCode();
        $qrPath     = $this->generateQrCode($ticketCode);

        return CinemaTicket::create([
            'cinema_order_item_id' => $item->id,
            'user_id'              => $order->user_id,
            'ticket_code'          => $ticketCode,
            'qr_code'              => $qrPath,
            'movie_title'          => $movie->title,
            'cinema_name'          => $cinema->name,
            'studio_name'          => $studio->name,
            'seat_number'          => $seat->seat_number,
            'seat_type'            => $seat->seat_type,
            'ticket_type'          => $item->ticket_type,
            'price'                => $item->price,
            'show_time'            => $showtime->start_time,
            'format'               => $showtime->format,
            'language'             => $showtime->language,
            'holder_name'          => $order->user->name,
            'holder_email'         => $order->user->email,
            'status'               => 'active',
        ]);
    }

    // Generate QR code PNG
    private function generateQrCode(string $ticketCode): string
    {
        $qrContent = json_encode([
            'code'  => $ticketCode,
            'type'  => 'cinema',
            'ts'    => now()->timestamp,
        ]);

        $qrImage = QrCode::format('png')
            ->size(400)
            ->errorCorrection('H')
            ->margin(2)
            ->generate($qrContent);

        $path = 'cinema/tickets/qr/' . $ticketCode . '.png';
        Storage::disk('public')->put($path, $qrImage);

        return $path;
    }

    // Generate unique ticket code
    private function generateTicketCode(): string
    {
        do {
            $code = 'CIN-'
                . strtoupper(Str::random(4))
                . '-'
                . strtoupper(Str::random(6));
        } while (CinemaTicket::where('ticket_code', $code)->exists());

        return $code;
    }

    // Validate tiket saat scan di pintu masuk
    public function validateTicket(string $ticketCode): array
    {
        $ticket = CinemaTicket::where('ticket_code', $ticketCode)
            ->with(['orderItem.cinemaOrder.showtime'])
            ->first();

        // Tidak ditemukan
        if (!$ticket) {
            return [
                'valid'   => false,
                'status'  => 'not_found',
                'message' => 'Kode tiket tidak ditemukan.',
                'ticket'  => null,
            ];
        }

        // Sudah digunakan
        if ($ticket->status === 'used') {
            return [
                'valid'   => false,
                'status'  => 'already_used',
                'message' => 'Tiket sudah digunakan pada '
                    . $ticket->used_at?->format('d M Y H:i'),
                'ticket'  => $ticket,
            ];
        }

        // Dibatalkan
        if ($ticket->status === 'cancelled') {
            return [
                'valid'   => false,
                'status'  => 'cancelled',
                'message' => 'Tiket telah dibatalkan.',
                'ticket'  => $ticket,
            ];
        }

        // Expired
        if ($ticket->status === 'expired') {
            return [
                'valid'   => false,
                'status'  => 'expired',
                'message' => 'Tiket sudah kadaluarsa.',
                'ticket'  => $ticket,
            ];
        }

        // Bukan hari tayang
        if (!$ticket->show_time?->isToday()) {
            $diff = now()->diffInDays($ticket->show_time, false);
            $msg  = $diff > 0
                ? "Film ditayangkan {$diff} hari lagi ({$ticket->show_time->format('d M Y')})."
                : "Film sudah ditayangkan pada {$ticket->show_time->format('d M Y')}.";

            return [
                'valid'   => false,
                'status'  => 'wrong_date',
                'message' => $msg,
                'ticket'  => $ticket,
            ];
        }

        // Valid — mark as used
        $ticket->update([
            'status'  => 'used',
            'used_at' => now(),
        ]);

        Log::info('Cinema ticket validated', [
            'ticket_code' => $ticketCode,
            'seat'        => $ticket->seat_number,
            'movie'       => $ticket->movie_title,
        ]);

        return [
            'valid'   => true,
            'status'  => 'valid',
            'message' => 'Tiket valid! Selamat menikmati film.',
            'ticket'  => $ticket,
        ];
    }

    // Cancel semua tiket dalam order
    public function cancelTickets(CinemaOrder $order): int
    {
        $count = CinemaTicket::whereHas('orderItem', function ($q) use ($order) {
            $q->where('cinema_order_id', $order->id);
        })
            ->where('status', '!=', 'used')
            ->update(['status' => 'cancelled']);

        return $count;
    }

    // Expire tiket yang showtimenya sudah lewat
    public function expireOldTickets(): int
    {
        $count = CinemaTicket::where('status', 'active')
            ->where('show_time', '<', now()->subHours(3))
            ->update(['status' => 'expired']);

        if ($count > 0) {
            Log::info("Expired {$count} cinema ticket(s).");
        }

        return $count;
    }
}
