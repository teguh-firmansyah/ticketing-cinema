<x-cinema-layout>
    <x-slot name="title">
        E-Ticket {{ $ticket->ticket_code }}
        — {{ setting('app_name') }} Cinema
    </x-slot>

    <div class="max-w-lg mx-auto px-4 sm:px-6 py-8">

        {{-- Back --}}
        <a href="{{ route('cinema.my-tickets') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500
            hover:text-white transition-colors duration-200 mb-6">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke Tiket Saya
        </a>

        {{-- E-Ticket Card --}}
        <div class="relative" id="ticket-card">

            {{-- Main ticket --}}
            <div
                class="bg-gray-900 border border-white/10 rounded-3xl overflow-hidden
            shadow-2xl shadow-black/60">

                {{-- Top: Movie backdrop --}}
                <div class="relative h-36 bg-gray-800 overflow-hidden">
                    <img src="{{ $ticket->orderItem->cinemaOrder->showtime->movie->backdrop_url }}"
                        class="w-full h-full object-cover opacity-60" alt="{{ $ticket->movie_title }}">
                    <div class="absolute inset-0 bg-gradient-to-b
                    from-transparent to-gray-900">
                    </div>

                    {{-- Status badge --}}
                    @php
                        $sCfg = [
                            'active' => ['bg' => 'bg-emerald-500', 'text' => 'text-white', 'label' => '✓ Tiket Aktif'],
                            'used' => ['bg' => 'bg-blue-500', 'text' => 'text-white', 'label' => '✓ Sudah Digunakan'],
                            'cancelled' => ['bg' => 'bg-red-600', 'text' => 'text-white', 'label' => '✗ Dibatalkan'],
                            'expired' => ['bg' => 'bg-gray-600', 'text' => 'text-white', 'label' => '✗ Kadaluarsa'],
                        ];
                        $sc = $sCfg[$ticket->status] ?? $sCfg['active'];
                    @endphp
                    <div class="absolute top-4 right-4">
                        <span
                            class="text-xs font-bold px-3 py-1.5 rounded-full
                        {{ $sc['bg'] }} {{ $sc['text'] }}">
                            {{ $sc['label'] }}
                        </span>
                    </div>

                    {{-- App logo --}}
                    <div class="absolute top-4 left-4 flex items-center gap-2">
                        <div
                            class="w-7 h-7 bg-red-600 rounded-lg flex items-center
                        justify-center">
                            <i class="ti ti-movie text-white text-sm"></i>
                        </div>
                        <span class="text-white text-xs font-bold">
                            {{ setting('app_name', config('app.name')) }}
                        </span>
                    </div>

                </div>

                {{-- Movie info --}}
                <div class="px-6 pt-5 pb-4 border-b border-dashed border-white/10">
                    <div class="flex items-start gap-4">
                        {{-- Poster --}}
                        <div
                            class="w-16 h-24 bg-gray-800 rounded-xl overflow-hidden
                        flex-shrink-0 border border-white/10 -mt-10 relative z-10
                        shadow-xl">
                            <img src="{{ $ticket->orderItem->cinemaOrder->showtime->movie->poster_url }}"
                                class="w-full h-full object-cover" alt="{{ $ticket->movie_title }}">
                        </div>
                        <div class="flex-1 min-w-0 pt-1">
                            <h2 class="text-lg font-black text-white leading-tight mb-1">
                                {{ $ticket->movie_title }}
                            </h2>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span
                                    class="text-[10px] font-bold bg-blue-900/40
                                border border-blue-500/40 text-blue-400
                                px-2 py-0.5 rounded-full">
                                    {{ strtoupper($ticket->format) }}
                                </span>
                                <span
                                    class="text-[10px] bg-white/5 border border-white/10
                                text-gray-500 px-2 py-0.5 rounded-full">
                                    {{ ucfirst($ticket->language) }}
                                </span>
                                <span
                                    class="text-[10px] bg-white/5 border border-white/10
                                text-gray-500 px-2 py-0.5 rounded-full">
                                    {{ $ticket->ticket_type_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail info grid --}}
                <div class="px-6 py-4 border-b border-dashed border-white/10">
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Left column --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] text-gray-600 uppercase tracking-widest mb-1">
                                    Bioskop
                                </p>
                                <p class="text-sm font-bold text-white leading-snug">
                                    {{ $ticket->cinema_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-600 uppercase tracking-widest mb-1">
                                    Studio
                                </p>
                                <p class="text-sm font-bold text-white">
                                    {{ $ticket->studio_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-600 uppercase tracking-widest mb-1">
                                    Tanggal
                                </p>
                                <p class="text-sm font-bold text-white">
                                    {{ $ticket->show_time->translatedFormat('D, d M Y') }}
                                </p>
                            </div>
                        </div>

                        {{-- Right column --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] text-gray-600 uppercase tracking-widest mb-1">
                                    Kursi
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="text-3xl font-black text-red-400">
                                        {{ $ticket->seat_number }}
                                    </span>
                                    <span
                                        class="text-[10px] font-semibold px-2 py-0.5
                                    rounded-lg border
                                    {{ in_array($ticket->seat_type, ['vip', 'couple'])
                                        ? 'bg-violet-900/30 border-violet-500/30 text-violet-400'
                                        : 'bg-gray-800 border-white/10 text-gray-500' }}">
                                        {{ ucfirst($ticket->seat_type) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-600 uppercase tracking-widest mb-1">
                                    Jam Tayang
                                </p>
                                <p class="text-2xl font-black text-white">
                                    {{ $ticket->show_time->format('H:i') }}
                                    <span class="text-sm text-gray-600 font-normal">WIB</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-600 uppercase tracking-widest mb-1">
                                    Harga
                                </p>
                                <p class="text-sm font-bold text-white">
                                    Rp {{ number_format($ticket->price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Holder info --}}
                <div class="px-6 py-3 bg-white/[0.02] border-b border-dashed border-white/10">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-full bg-red-600/20 border border-red-500/20
                        flex items-center justify-center text-red-400 text-xs font-bold
                        flex-shrink-0">
                            {{ strtoupper(substr($ticket->holder_name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-white truncate">
                                {{ $ticket->holder_name }}
                            </p>
                            <p class="text-[10px] text-gray-600 truncate">
                                {{ $ticket->holder_email }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] text-gray-600">Order</p>
                            <p class="text-[10px] font-mono text-gray-500">
                                {{ $ticket->orderItem->cinemaOrder->order_number }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- QR Code --}}
                <div class="px-6 pt-5 pb-6">
                    @if ($ticket->status === 'active')

                        <div class="text-center mb-4">
                            <p class="text-xs text-gray-500 mb-1">
                                Tunjukkan QR code ini kepada petugas di pintu masuk studio
                            </p>
                        </div>

                        {{-- QR container --}}
                        <div class="relative mx-auto w-52">
                            <div
                                class="w-52 h-52 bg-white rounded-2xl flex items-center
                        justify-center p-3 shadow-lg mx-auto">
                                @if ($ticket->qr_code)
                                    <img src="{{ route('cinema.tickets.qr', $ticket->id) }}"
                                        class="w-full h-full object-contain" alt="QR Code {{ $ticket->ticket_code }}"
                                        id="qr-image">
                                @else
                                    {{-- Fallback: generate QR di client --}}
                                    <div id="qr-fallback" class="w-full h-full"></div>
                                @endif
                            </div>

                            {{-- Corner decorations --}}
                            <div
                                class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2
                        border-red-500 rounded-tl-lg">
                            </div>
                            <div
                                class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2
                        border-red-500 rounded-tr-lg">
                            </div>
                            <div
                                class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2
                        border-red-500 rounded-bl-lg">
                            </div>
                            <div
                                class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2
                        border-red-500 rounded-br-lg">
                            </div>
                        </div>

                        {{-- Ticket code --}}
                        <div class="text-center mt-4">
                            <p class="text-sm font-mono font-black text-white tracking-[3px]">
                                {{ $ticket->ticket_code }}
                            </p>
                            <p class="text-[10px] text-gray-600 mt-1">
                                Valid untuk 1x penggunaan
                            </p>
                        </div>
                    @elseif($ticket->status === 'used')
                        {{-- Used state --}}
                        <div class="text-center py-6">
                            <div
                                class="w-16 h-16 bg-blue-500/10 rounded-full flex items-center
                        justify-center mx-auto mb-3">
                                <i class="ti ti-circle-check text-blue-400 text-3xl"></i>
                            </div>
                            <p class="text-base font-bold text-blue-400 mb-1">
                                Tiket Telah Digunakan
                            </p>
                            @if ($ticket->used_at)
                                <p class="text-xs text-gray-600">
                                    Digunakan pada
                                    {{ $ticket->used_at->translatedFormat('D, d M Y — H:i') }}
                                </p>
                            @endif
                            <div
                                class="mt-4 font-mono text-sm font-black text-gray-600
                        tracking-[3px]">
                                {{ $ticket->ticket_code }}
                            </div>
                        </div>
                    @else
                        {{-- Cancelled/Expired --}}
                        <div class="text-center py-6">
                            <div
                                class="w-16 h-16 bg-gray-700/30 rounded-full flex items-center
                        justify-center mx-auto mb-3">
                                <i class="ti ti-ticket-off text-gray-600 text-3xl"></i>
                            </div>
                            <p class="text-base font-bold text-gray-500 mb-1">
                                Tiket Tidak Valid
                            </p>
                            <p class="text-xs text-gray-600">
                                Tiket ini telah {{ $ticket->status === 'expired' ? 'kadaluarsa' : 'dibatalkan' }}
                            </p>
                            <div
                                class="mt-4 font-mono text-sm font-black text-gray-700
                        tracking-[3px] line-through">
                                {{ $ticket->ticket_code }}
                            </div>
                        </div>

                    @endif
                </div>

                {{-- Bottom barcode-style decoration --}}
                <div class="px-6 pb-5">
                    <div class="flex items-center gap-0.5 justify-center opacity-10">
                        @for ($i = 0; $i < 40; $i++)
                            <div class="bg-white rounded-sm"
                                style="width: {{ rand(1, 3) }}px; height: {{ rand(12, 28) }}px">
                            </div>
                        @endfor
                    </div>
                </div>

            </div>

            {{-- Punch holes --}}
            <div class="absolute left-0 right-0 flex justify-between px-0" style="top: calc(36px + 9rem + 1px)">
                <div class="w-5 h-5 bg-gray-950 rounded-full -translate-x-1/2
                border-r border-white/5">
                </div>
                <div class="w-5 h-5 bg-gray-950 rounded-full translate-x-1/2
                border-l border-white/5">
                </div>
            </div>

        </div>

        {{-- Action Buttons --}}
        @if ($ticket->status === 'active')
            <div class="mt-5 grid grid-cols-2 gap-3">

                {{-- Download QR --}}
                @if ($ticket->qr_code)
                    <a href="{{ route('cinema.tickets.qr', $ticket->id) }}"
                        download="ticket-{{ $ticket->ticket_code }}.png"
                        class="flex items-center justify-center gap-2 h-12 bg-gray-900
                border border-white/10 hover:border-white/20 text-gray-400
                hover:text-white text-sm font-medium rounded-2xl
                transition-all duration-200">
                        <i class="ti ti-download text-base"></i>
                        Download QR
                    </a>
                @endif

                {{-- Share --}}
                <button onclick="shareTicket()"
                    class="flex items-center justify-center gap-2 h-12 bg-gray-900
                border border-white/10 hover:border-white/20 text-gray-400
                hover:text-white text-sm font-medium rounded-2xl
                transition-all duration-200">
                    <i class="ti ti-share text-base"></i>
                    Bagikan
                </button>

                {{-- Add to wallet (placeholder) --}}
                <button onclick="alert('Fitur segera hadir!')"
                    class="col-span-2 flex items-center justify-center gap-2 h-12
                bg-gray-900 border border-white/10 hover:border-white/20
                text-gray-400 hover:text-white text-sm font-medium rounded-2xl
                transition-all duration-200">
                    <i class="ti ti-wallet text-base"></i>
                    Simpan ke Wallet
                </button>

            </div>

            {{-- Reminder --}}
            <div class="mt-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <i class="ti ti-bell text-amber-400 text-base flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-xs font-semibold text-amber-300 mb-1">
                            Pengingat Penting
                        </p>
                        <ul class="text-xs text-amber-200/70 space-y-1 list-disc list-inside">
                            <li>Datang 15-30 menit sebelum film dimulai</li>
                            <li>Pastikan layar HP cukup terang saat scan</li>
                            <li>Tiket hanya berlaku 1x penggunaan</li>
                            <li>Simpan QR code — jangan bagikan ke orang lain</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Order link --}}
        <div class="mt-4 text-center">
            <a href="{{ route('cinema.orders.show', $ticket->orderItem->cinemaOrder->id) }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600
                hover:text-white transition-colors duration-200">
                <i class="ti ti-shopping-cart text-sm"></i>
                Lihat Detail Order
                <i class="ti ti-arrow-right text-xs"></i>
            </a>
        </div>

    </div>

    @push('scripts')
        <script>
            // Share ticket
            async function shareTicket() {
                const data = {
                    title: 'E-Ticket Bioskop',
                    text: '{{ $ticket->movie_title }} — Kursi {{ $ticket->seat_number }} — {{ $ticket->show_time->format('d M Y H:i') }}',
                    url: window.location.href,
                };

                if (navigator.share) {
                    try {
                        await navigator.share(data);
                    } catch (err) {
                        if (err.name !== 'AbortError') {
                            copyToClipboard();
                        }
                    }
                } else {
                    copyToClipboard();
                }
            }

            function copyToClipboard() {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    const btn = document.querySelector('[onclick="shareTicket()"]');
                    if (btn) {
                        btn.innerHTML =
                            '<i class="ti ti-check text-base text-emerald-400"></i><span class="text-emerald-400">Link Disalin!</span>';
                        setTimeout(() => {
                            btn.innerHTML = '<i class="ti ti-share text-base"></i>Bagikan';
                        }, 2000);
                    }
                });
            }

            // QR fallback jika gambar gagal load
            @if (!$ticket->qr_code)
                // Generate QR di client jika tidak ada di server
                document.addEventListener('DOMContentLoaded', () => {
                    const container = document.getElementById('qr-fallback');
                    if (!container) return;
                    // Placeholder jika library tidak tersedia
                    container.innerHTML = `
        <div class="w-full h-full flex flex-col items-center justify-center gap-2">
            <i class="ti ti-qrcode text-gray-400 text-5xl"></i>
            <p class="text-[10px] text-gray-500 text-center">
                QR Code sedang<br>digenerate...
            </p>
        </div>
    `;
                });
            @endif
        </script>
    @endpush

</x-cinema-layout>
